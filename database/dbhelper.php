<?php
require_once('config.php');

// Helper function to get a database connection
function getDbConnection() {
    $con = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
    
    // Check connection
    if (mysqli_connect_error()) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $con;
}

// Helper function to handle SQL errors
function handleSqlError($con, $sql, $function_name) {
    $error_msg = "SQL Error in {$function_name}(): " . mysqli_error($con) . " | SQL: " . $sql;
    error_log($error_msg);
    
    // For development - show error on screen
    if (defined('DEBUG') && DEBUG) {
        echo "Database Error: " . mysqli_error($con) . "<br>SQL: " . $sql;
    } else {
        echo "Database Error occurred. Please check the logs.";
    }
}

// Function to execute query and return affected rows count
function executeWithAffectedRows($sql) {
    $con = getDbConnection();
    
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        handleSqlError($con, $sql, 'executeWithAffectedRows');
        mysqli_close($con);
        return false;
    }
    
    $affected_rows = mysqli_affected_rows($con);
    mysqli_close($con);
    
    return $affected_rows;
}

function execute($sql, $params = [])
{
	//save data into table
	// open connection to database
	$con = getDbConnection();
	
	if (!empty($params)) {
		// Use prepared statement for SQL with parameters
		$stmt = mysqli_prepare($con, $sql);
		if (!$stmt) {
			handleSqlError($con, $sql, 'execute - prepare');
			mysqli_close($con);
			return false;
		}
		
		// Build type string and bind parameters
		$types = str_repeat('s', count($params));
		mysqli_stmt_bind_param($stmt, $types, ...$params);
		
		// Execute the statement
		$result = mysqli_stmt_execute($stmt);
		if (!$result) {
			handleSqlError($con, $sql, 'execute - execute');
		}
		
		mysqli_stmt_close($stmt);
	} else {
		// Direct query without parameters
		$result = mysqli_query($con, $sql);
		
		// Check if query was successful
		if (!$result) {
			handleSqlError($con, $sql, 'execute');
		}
	}

	//close connection
	mysqli_close($con);
	
	return $result;
}

function executeResult($sql, $params = []) {
    $con = getDbConnection();
    $data = [];

    if (!empty($params)) {
        // Use prepared statement for SQL with parameters
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            handleSqlError($con, $sql, 'executeResult - prepare');
            mysqli_close($con);
            return $data;
        }

        // Build type string and bind parameters
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);

        // Execute the statement
        if (!mysqli_stmt_execute($stmt)) {
            handleSqlError($con, $sql, 'executeResult - execute');
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            return $data;
        }

        // Check if mysqli_stmt_get_result is available (requires mysqlnd)
        if (function_exists('mysqli_stmt_get_result')) {
            // Use get_result if available
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                handleSqlError($con, $sql, 'executeResult - get_result');
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                return $data;
            }

            // Fetch results using mysqli_fetch_assoc
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
                // Limit the number of rows fetched to prevent memory exhaustion
                if (count($data) >= 1000) {
                    break;
                }
            }
            mysqli_free_result($result);
        } else {
            // Fallback for servers without mysqlnd - use store_result
            if (!mysqli_stmt_store_result($stmt)) {
                handleSqlError($con, $sql, 'executeResult - store_result');
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                return $data;
            }

            // Get metadata
            $meta = mysqli_stmt_result_metadata($stmt);
            if (!$meta) {
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                return $data;
            }

            // Create variables for binding
            $fields = [];
            $row = [];
            while ($field = mysqli_fetch_field($meta)) {
                $fields[] = &$row[$field->name];
            }
            mysqli_free_result($meta);

            // Bind result
            if (!empty($fields)) {
                call_user_func_array(array($stmt, 'bind_result'), $fields);
            }

            // Fetch all rows
            while (mysqli_stmt_fetch($stmt)) {
                // Convert to associative array
                $result_row = [];
                foreach ($row as $key => $value) {
                    $result_row[$key] = $value;
                }
                $data[] = $result_row;

                // Limit the number of rows fetched to prevent memory exhaustion
                if (count($data) >= 1000) {
                    break;
                }
            }
        }

        mysqli_stmt_close($stmt);
    } else {
        // Direct query without parameters
        $result = mysqli_query($con, $sql);

        // Check if query was successful
        if (!$result) {
            handleSqlError($con, $sql, 'executeResult');
            mysqli_close($con);
            return $data; // Return empty array
        }

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $data[] = $row;

            // Limit the number of rows fetched to prevent memory exhaustion
            if (count($data) >= 1000) {
                break;
            }
        }
        
        // Free result memory
        mysqli_free_result($result);
    }

    mysqli_close($con);
    return $data;
}

function executeSingleResult($sql, $params = []) {
    $con = getDbConnection();

    if (!empty($params)) {
        // Use prepared statement for SQL with parameters
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            handleSqlError($con, $sql, 'executeSingleResult - prepare');
            mysqli_close($con);
            return null;
        }

        // Build type string and bind parameters
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);

        // Execute the statement
        if (!mysqli_stmt_execute($stmt)) {
            handleSqlError($con, $sql, 'executeSingleResult - execute');
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            return null;
        }

        // Check if mysqli_stmt_get_result is available (requires mysqlnd)
        if (function_exists('mysqli_stmt_get_result')) {
            // Use get_result if available
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                handleSqlError($con, $sql, 'executeSingleResult - get_result');
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                return null;
            }
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
        } else {
            // Fallback for servers without mysqlnd - use store_result
            if (!mysqli_stmt_store_result($stmt)) {
                handleSqlError($con, $sql, 'executeSingleResult - store_result');
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                return null;
            }

            // Get metadata
            $meta = mysqli_stmt_result_metadata($stmt);
            if (!$meta) {
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                return null;
            }

            // Create variables for binding
            $fields = [];
            $row = [];
            while ($field = mysqli_fetch_field($meta)) {
                $fields[] = &$row[$field->name];
            }
            mysqli_free_result($meta);

            // Bind result
            if (!empty($fields)) {
                call_user_func_array(array($stmt, 'bind_result'), $fields);
            }

            // Fetch one row
            if (mysqli_stmt_fetch($stmt)) {
                // Convert to associative array
                $result_row = [];
                foreach ($row as $key => $value) {
                    $result_row[$key] = $value;
                }
                $row = $result_row;
            } else {
                $row = null;
            }
        }

        mysqli_stmt_close($stmt);
    } else {
        // Direct query without parameters
        $result = mysqli_query($con, $sql);

        // Check if query was successful
        if (!$result) {
            handleSqlError($con, $sql, 'executeSingleResult');
            mysqli_close($con);
            return null;
        }

        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
    }

    // Close connection
    mysqli_close($con);
    return $row;
}
function executeInsert($sql, $params = []) {
    $con = getDbConnection();
    
    if (!empty($params)) {
		// Use prepared statement for SQL with parameters
		$stmt = mysqli_prepare($con, $sql);
		if (!$stmt) {
			handleSqlError($con, $sql, 'executeInsert - prepare');
			mysqli_close($con);
			return null;
		}
		
		// Build type string and bind parameters
		$types = str_repeat('s', count($params));
		mysqli_stmt_bind_param($stmt, $types, ...$params);
		
		// Execute the statement
		$result = mysqli_stmt_execute($stmt);
		if ($result) {
			// Lấy ID của bản ghi vừa thêm vào
			$insertedId = mysqli_insert_id($con);
		} else {
			handleSqlError($con, $sql, 'executeInsert - execute');
			$insertedId = null;
		}
		
		mysqli_stmt_close($stmt);
	} else {
		// Direct query without parameters
		$result = mysqli_query($con, $sql);
		
		if ($result) {
			// Lấy ID của bản ghi vừa thêm vào
			$insertedId = mysqli_insert_id($con);
		} else {
			handleSqlError($con, $sql, 'executeInsert');
			$insertedId = null;
		}
	}

    mysqli_close($con);
    return $insertedId;
}
