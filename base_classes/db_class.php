<?php
// db_class.php -  Thu Mar 1 09:49:47 CST 2018
//   This is a rewrite/cleanup of the db_class I have been using.

// Since the calling files already have essentials required, requiring
// it again conflicts. It seems odd since require_once is supposed to 
// only 
// require_once "../essentials.php";
//
//  2020-01-08 - I'm going to use this project to make a postgresql version 
//    of the database interacter.
//  

require_once "db_config.php";
  
/////////////////////
// Encapsulating a safe usage of MySQL using mysqli 
class db_class 
{
    protected  $conn;
    protected  $stmnt_name;
	
 // initiator     
    function db_class(/* we might pass defaults here */) {                
        global $DATABASE;
        global $DB_USER;
        global $DB_PASSWORD;
        global $DB_SERVER;

        try {
//              $DB_PORT? $DB_SERVER?
            $this->conn = pg_connect("host=localhost dbname=$DATABASE user=$DB_USER password=$DB_PASSWORD");
            
            if (! $this->conn) {
                throw new Exception('Database connection failed. ' );
            }
        } catch(Exception $e) {
            echo "problem connecting to the database: " . $e->getLine() . ' - ' . $e->getMessage();
        }
    }
   

  /////////////////////////////////
  // Basic description, because it's useful...    
    function tableDescription($table_title) {
         $sql = "SELECT COLUMN_NAME column_name, data_type, character_maximum_length ";
         $sql .= "FROM information_schema.COLUMNS WHERE	TABLE_NAME = '$table_title'";
        
        return $this->getTableNoParams($sql);
    }

  /////////////////////////////////
  // It is used for getting the types of a table's columns to use in
  // a bound sql query. Sets text to s, whole numbers to i, decimals 
  // to d, and binary data to b. It returns a has of column names and 
  // type declaration 
    function columnTypeHash($table_title) {
        $desc_table = $this->tableDescription($table_title);

        $descHash = array() ; // for 5.3 compatablility 

        foreach($desc_table as $col_desc) {
            $type = $col_desc['data_type'];
        
            $descHash[$col_desc['column_name']] = $this->getTypeChar($type);
        }
        
        return $descHash;
    }

  /////////////////////////////////
  // Basically, this is the same as the previous function, but
  // It also adds the actual type, so one can ensure the correct 
  // date format.
    function columnTypeHashLong($table_title) {
        $desc_table = $this->tableDescription($table_title);
        $descHash = array() ; // for 5.3 compatablility 

        foreach($desc_table as $col_desc) {
            $type = $col_desc['data_type'];
        
            $descHash[$col_desc['column_name']] = array('data_type'=> $type, 
                                          'typeChar' => $this->getTypeChar($type));
        }
        
        return $descHash;
    }

  /////////////////////////////////
  // Get the character for proper SQL binding.
    function getTypeChar($type) {
        $typeChar = 's';  // default 
        if(preg_match("/int/i", $type) || preg_match("/bool/i", $type)){
            $typeChar = 'i';
        } elseif (preg_match("/real/i", $type) || preg_match("/float/i", $type) 
               || preg_match("/dec/i", $type) || preg_match("/numer/i", $type) 
               || preg_match("/doub/i", $type)) {
            $typeChar = 'd';
        }elseif (preg_match("/bin/i", $type) || preg_match("/image/i", $type) ) {
            $typeChar = 'b';
        }
        
        return $typeChar;
    }  
  
  /////////////////////////////////
  // PRIMARY KEY 
  function getPrimaryKey( $table ){
    global $DATABASE;
    $sql = "SELECT c.column_name, c.ordinal_position
			FROM information_schema.key_column_usage AS c
			LEFT JOIN information_schema.table_constraints AS t
			ON t.constraint_name = c.constraint_name
			WHERE t.table_name = '$table' AND t.constraint_type = 'PRIMARY KEY';";
              
    $db_table = $this->getTableNoParams($sql);

    return $db_table[0]['column_name'];
  }
  
  /////////////////////////////////
  // GET A QUICK COUNT  
  function tableCount( $table ){
      $primary_key = $this->getPrimaryKey($table);
      
      $sql = "SELECT COUNT(" . $primary_key . ") FROM " . $table;
      
      $db_table = $this->getTableNoParams($sql);
      showArray($db_table);
      return $db_table[0]["count"];
  }
  
  /////////////////////////////////
  // DETERMINE WHETHER A ROW EXISTS
    function rowExists($table, $id) {
    	$primary_key = $this->getPrimaryKey($table);
    	
        $sql = "SELECT EXISTS(SELECT 1 FROM $table WHERE $primary_key=$1)";
        $prep_result = pg_prepare($this->conn, $this->stmnt_name, $sql);

        $exe_result  = pg_execute($this->conn, $this->stmnt_name, array($id));
            
        $fetch_result = pg_fetch_assoc($exe_result);
        
        $exists = ($fetch_result['exists'] == 't')? true:false;
        return $exists;
    }
    
/////////////////////////////////
// I guess this is still an interesting wrapper. It assumes the table has 
// a primary key called, 'id'
	function getRowByID($id, $table) {
	    $sql = "SELECT * FROM $table WHERE id=$1";
	    
	    return $this->simpleOneParamRequest_pg($sql, $id);
	}
  
  /////////////////////////////////
  // Returns an array of rows, represented as associative arrays with 
  // column => values
    function getTableNoParams($sql) {
        try {
            
            $query_result = pg_query($this->conn, $sql);
			$result_array = pg_fetch_all($query_result);  
			                      
            if(empty($result_array)){
                throw new Exception(pg_last_error($this->conn));
            } 
            
            return $result_array; 
            
        } catch (Exception $e) {
            echo "Fail in getTableNoParams $sql: " . $e->getLine() . 
                 ": " . $this->conn->error;;
            return null;
        }
        
        // Older versions of db_class check to see if $this->has_native_driver
        // is true. From here on out, we won't worry too much about it. 
    }


  /////////////////////////////////
  // A wrapper for porting from mysql 
    function simpleOneParamRequest($sql, $type, $queryVal) {
    	$updated_sql = preg_replace("/\?/", '\$1',$sql);
    	
    	return $this->simpleOneParamRequest_pg($updated_sql, $queryVal);
    }
    

  /////////////////////////////////
  // used to get only a couple of columns from a row 
    function simpleOneParamRequest_pg($sql, $queryVal) {
        
        try {
            $prep_result = pg_prepare($this->conn, 'current_query', $sql);
            
            if (! $prep_result) {
                throw new Exception("Failed to prepare $sql");
            }    
            
            $exe_result  = pg_execute($this->conn, 'current_query', array($queryVal));
            if (! $exe_result) {
                throw new Exception("Failed to execute $sql");
            }    
//             $the_array   = pg_fetch_row($exe_result); // works, but isn't associative
            $the_array   = pg_fetch_array($exe_result, 0, PGSQL_ASSOC);
//             $the_array   = pg_fetch_array($exe_result); // works, but gives too much info 
            if (! $prep_result) {
                throw new Exception("Failed to fetch $sql");
            }    
            
            return $the_array ;
        } catch (Exception $e) {
            echo "Fail in simpleOneParamRequest_pg $sql: " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }


/////////////////////////////////
// For quick updates when I have FULL CONTROL of the parameters
// and no parameters are being passed that need to be properly 
// bound.
    function simpleExecute($sql) {  //NOTE: I am here 
    //NOTE: I need to understand this a little better. 
        try {
            $$prep_result = pg_prepare($this->conn, 'current_query', $sql);
            if (! $stmt) {
                throw new Exception();
            }    
            //NOTE: I DON'T think you can do this without passing a parameter array
            $result = $stmt->execute();
            if(empty($result)){
                throw new Exception('failed execute');
            } 
          } catch (Exception $e) {
            echo "Fail in prepBindExOneParam $sql: " . $e->getLine()  . 
                  ": " . $this->conn->error .  ": " . $e->getMessage();
            return null;        
        }
          
        return $this->conn->affected_rows;
    }
    



/////////////////////////////////
// e.g. delete a a row 
//NOTE: So far, deleting a row is the only thing it is used for. 
    function simpleOneParamUpdate($sql, $type, $queryVal) {
        $parsed_sql = preg_split("/\s+/", $sql);
        
		showArray($parsed_sql);
		// Check to see if this is a delete statement. 
		if(preg_match("/DELETE/i", $parsed_sql[0])){
			$table = $parsed_sql[2];
			
			// build the associative array for the parameters
			$param_name = array_pop($parsed_sql);
			$param_name = preg_replace("/=\?/", "", $param_name);
			showDebug($param_name);
			
			return $this->deleteOneRow_pg($table, array($param_name => $queryVal));
			
		} else {
			showDebug("We need to take care of theis scenario: " . $parsed_sql[0]);
		}
		
//         return pg_affected_rows($result);
    }
    
/////////////////////////////////
// 
    function deleteOneRow_pg($table, $assoc_array)  {
    	$result = pg_delete($this->conn, $table, $assoc_array);
    	$affected_rows = pg_affected_rows($result);
    	
    	showDebug("affected_rows: " .  $affected_rows);
    }


  /////////////////////////////////
  // Preparee, bind, execute, and return the mysqli_result
  // NOTE: is this obsolete???
//     function  prepBindExOneParam($sql, $type, $queryVal){
//         try {
//             $stmt = $this->conn->prepare($sql);
//             if (! $stmt) {
//                 throw new Exception();
//             }    
//             $result = $stmt->bind_param($type, $queryVal);    
//             if(empty($result)){
//                 throw new Exception('failed bind_param');
//             } 
//             $result = $stmt->execute();
//             if(empty($result)){
//                 throw new Exception('failed execute');
//             } 
//             return $stmt;
//             
//         } catch (Exception $e) {
//             echo "Fail in prepBindExOneParam $sql: " . $e->getLine()  . 
//                   ": " . $this->conn->error .  ": " . $e->getMessage();
//             return null;        
//         }
//     }

  //////////////////////////////
  // This is like safeInsertUpdateDelete except that it will return a table. 
  //  It is useful for more selective or inclusive search queries 
    function safeSelect($sql, $typeStr, $paramList) {
		$pg_sql = $this->mysql2Postgres($sql);
		$prep_result = pg_prepare($this->conn, 'current_transaction', $pg_sql);
		$exe_result  = pg_execute($this->conn, 'current_transaction', $paramList);

		$the_array   = pg_fetch_array($exe_result, 0, PGSQL_ASSOC);
		
		return $the_array;
//         $stmt = $this->prepBindExMultiParams($sql, $typeStr, $paramList);
//         
//         $mysqli_result = $stmt->get_result();
//         return $mysqli_result->fetch_all(MYSQLI_ASSOC); 
    }

  /////////////////////////////////
  // create a safe sql statement and execute it 
  // This is the workhorse
  /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
    function safeInsertUpdateDelete($sql, $typeStr, $paramList) {
        $pg_sql = $this->mysql2Postgres($sql);
        return $this->safeInsertUpdateDelete_pg($pg_sql,  $paramList);
    }

  /////////////////////////////////
  // create a safe sql statement and execute it 
  // This is the workhorse
  /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
    function safeInsertUpdateDelete_pg($my_sql,  $paramList) {
        try {
        	// convert the mysqli formatted SQL string into something postgresql can use.
        	//  
//         	showDebug($my_sql);
//         	$exploded = str_split($my_sql);
// //         	showArray($exploded);
//         	$param_count = 1;
//         	$pg_sql = '';
//         	foreach($exploded as $char){
//         		if($char == '?'){
//         			$char = '$' . "$param_count";
//         			$param_count++;
//         		}
//         		$pg_sql .= $char;
//         	}
			
			// prepare the statement 
			$prep_result = pg_prepare($this->conn, 'current_transaction', $pg_sql);
			
			// execute
			$exe_result  = pg_execute($this->conn, 'current_transaction', $paramList);
						
			return pg_affected_rows ( $exe_result ) ;
			
        } catch (Exception $e) {
            echo "Fail in safeInsertUpdateDelete $sql: " . $e->getLine() . 
                 ": " . $this->conn->error;
            return null;        
        }
       
    }

/////////////////////////////////
// Convert a mysqli SQL statement to a PostgreSQL statement 
	function mysql2Postgres($my_sql){
		$exploded = str_split($my_sql);
//         	showArray($exploded);
		$param_count = 1;
		$pg_sql = '';
		foreach($exploded as $char){
			if($char == '?'){
				$char = '$' . "$param_count";
				$param_count++;
			}
			$pg_sql .= $char;
		}
		
		return $pg_sql;
	}


  /////////////////////////////////
  // Preparee, bind, execute, and return the mysqli_result
  // This is where most of the errors occur. 
    function  prepBindExMultiParams($sql, $typeStr, $paramList){
        try {
        	$pg_sql = $this->mysql2Postgres($sql);
        	$result = pg_prepare($this->conn, 'current_transaction', $pg_sql);
//             $stmt = $this->conn->prepare($sql);
//             if (! $stmt) {
//                 throw new Exception();
//             }    
//             
//             // MULTI BIND
//             // turn typeStr and paramList into one array? 
//             // TODO: better understand php callbackParams and call_user_func_array 
//             $callbackParams = array();
//             $callbackParams[] = & $typeStr;
//             // pass the getTableNoParamsresses of the escaped parameters to the statement 
//             $n = count($paramList);
//             for( $i=0; $i < $n; $i++ ) {
//                 $callbackParams[] = & $paramList[$i];
//             }
//             
//             $result = call_user_func_array(array($stmt, "bind_param"), $callbackParams);
//             if(!$result) {
//                 throw new Exception("Funk in the call_user_func_array ");
//             }
// 
//             $result = $stmt->execute();
//             if(empty($result)){
//                 throw new Exception();
//             } 
//             return $stmt;
            return array('result' => $result, 'current_transaction');
        } catch(Exception $e) {
            echo "Fail in prepBindExMultiParams $sql: " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }

  //////////////////////////////////////////////////////////
  // A few higher level functions

  //////////////////////////////
  // I've been creating insert/update commands for a 
  // while and they tend to be very similar. 
// TODO: does this need to be fixed in any way?
    function buildAndExecuteInsert($table, $data_elements, $auto_col_to_skip = '') {
        $typeHashLong = $this->columnTypeHashLong($table);
        
       // Build the insert string
        $sql = 'INSERT INTO ' . $table ;
        $columnStr = ' (';
        $valueStr = '(';
        $typeList = '';
        $paramList = array();
    
//         $debug = array();
       // build the data elements of the sql string
        foreach ($data_elements as $col => $val) {
//             $debug[] = $col;
            // Skip this column if it is the auto_increment (e.g. id)
            if($col == $auto_col_to_skip) {
                continue;
            } 
            
            if( array_key_exists($col, $typeHashLong) ){
                $typeStruct = $typeHashLong[$col];
                
                if( sizeof($paramList) > 0 ) {
                    $columnStr .= ',';
                    $valueStr  .= ',';
                }
                $columnStr .= $col;
                $valueStr  .= '?';
                $typeList  .= $typeStruct['typeChar'];
                $paramList[] = ensureType($val, $typeStruct['type']);
            }
        }
    
        $columnStr .= ')';
        $valueStr  .= ')';
        $sql .= $columnStr . ' VALUES ' . $valueStr;
        
//         return array( $sql, $typeList, $paramList );
        
        $result = $this->safeInsertUpdateDelete($sql, $typeList, $paramList);
        
        return $result;
    }

  //////////////////////////////
  // Updates are a little trickier because they tend to be more specific
  // this assumes that there is an id and it is the primary key and is 
  // an integer. The $data_elements array must have an id
    function buildAndExecuteUpdate($table, $data_elements ){
// TODO: does this need to be fixed in any way?
        try {
           // make sure there is an id.
            if( !array_key_exists('id', $data_elements) ){
                throw new Exception("No id in data_elements");
            }
            $id = $data_elements['id'];
           // Don't update the row's id. Unset it so it isn't 
           // included in the foreach.
            unset( $data_elements['id'] );
            
            $typeHashLong = $this->columnTypeHashLong($table);
            $sql = 'UPDATE ' . $table . ' SET ';
            
            $typeList = '';
            $paramList = array();
            
           // Which columns get set?
            foreach($data_elements as $col => $val) {
                if( array_key_exists($col, $typeHashLong) ){
                    $typeStruct = $typeHashLong[$col];
                    
                    if( sizeof($paramList) > 0 ){
                        $sql .= ',';
                    }
                    $sql .= "$col=?";
                    $typeList  .= $typeStruct['typeChar'];
                    $paramList[] = ensureType($data_elements[$col], $typeStruct['type']);
                }
            }
            
            $sql .= ' WHERE id=?';
            $typeList  .= 'i';
            $paramList[] = $id;
            
//             return array('typeHashLong'=> $typeHashLong, 'data_elements' => $data_elements);
//            return array($sql, $typeList, $paramList); 
            $result = $this->safeInsertUpdateDelete($sql, $typeList, $paramList);
            
            return $result;
        } catch (Exception $e) {
            echo "Fail in buildAndExecuteUpdate " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }

  //////////////////////////////
  ///
    function hasForeignKey($table, $column) {
        $table_desc = $this->tableDescription($table);
        
        foreach($table_desc as $col_desc){
            // find the column 
            if($col_desc['Field'] == $column) {
                // check if it has a MUL key
                if($col_desc['Key'] == 'MUL'){
                    return 1;
                }
            }
        }
        
        return 0;
    }

  //////////////////////////////
  /// Got this from https://stackoverflow.com/questions/806989/how-to-find-all-tables-that-have-foreign-keys-that-reference-particular-table-co
  // user sonance207  -- though altered heavily.
    function getFKeyParentTable($table, $column, $referenced_column='id') {
      $sql = "SELECT  ke.REFERENCED_TABLE_NAME parentTable, ke.TABLE_NAME childTable, ke.COLUMN_NAME ChildColumnName";
      $sql .= " FROM information_schema.KEY_COLUMN_USAGE ke";
      $sql .= " WHERE ke.referenced_table_name IS NOT NULL";
      $sql .= " AND ke.REFERENCED_COLUMN_NAME = '$referenced_column'";
      $sql .= " AND ke.TABLE_NAME = '$table'";
      $sql .= " AND ke.COLUMN_NAME =  '$column' ";
      
//       $stmt = $this->conn->prepare($sql);
//       return $stmt;
      $table = $this->getTableNoParams($sql);
      
      
      
      return $table[0]['parentTable'];
    }

    
  //////////////////////////////
  ///
    function getLastDBError() {
      return $this->conn->error_list;
    }
    
   /////////////////////////////////
   // Get the id of the last insert call with auto increment. 
   // It is up to the programmer to use it when appropriate.
     function lastInsertedID() {
        return $this->conn->insert_id;
     }
    

  /////////////////////////////////
  // escapes values like Pete "Mac" McKenzi  - as it turns out, this is not needed 
    function escapeVal($value) {
        return $this->conn->real_escape_string($value);
    }
    
  /////////////////////////////////
  // Gets the last row of a table (the most recently created in dho_users);
    function getLastRow($table) {
        try {
            $primary_key = $this->getPrimaryKey($table);
            $sql = "SELECT * FROM $table ORDER BY $primary_key DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            if (! $stmt) {
                throw new Exception();
            }    
            $result = $stmt->execute();
            if(empty($result)){
                throw new Exception();
            } 
            $mysqli_result = $stmt->get_result();
            return $mysqli_result->fetch_assoc();
        }catch(Exception $e) {
            echo "Fail in getLastRow: " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }

  /////////////////////////////////
  // final close - wrapper for closing the connection 
    function closeDB() {
        pg_close($this->conn);
    }
    
  /////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////
  // Let's see if we can group some static functions with this. Some of these 
  // will assume (for the moment anyway) that an object has been created 

  /////////////////////////////////
  // Generic Insert -- we have this up above 
//    public static function genInsertStatement($table, $col_val_hash, $colTypeHashLong){}

}

