<?php
    require_once("{$_SERVER['DOCUMENT_ROOT']}../../config/db_config.php");
    require_once("{$_SERVER['DOCUMENT_ROOT']}../../classes/Blog.php");

    $params = $_REQUEST['params'];
    $action = $params['action'];
    $id = isset($params['id']) ? $params['id'] : 0;
    $classObj = new Blog($db);
    if(method_exists($classObj,$action)) {
        switch ($action) {
            case 'getData' : {
                $res = $classObj->$action($id);
            } break;
            case 'getSliderPosts': {
                $res = $classObj->$action($params['postsNumber']);
            } break;
            case 'createComment':
            case 'createPost' : {
                $res = $classObj->$action($params['args']);
            } break;
            case 'getPost' : {
                $res = $classObj->$action($id);
            }
        }

    } else {
        $res = "404";
    }

    echo json_encode(utf8ize($res));


    function utf8ize( $mixed ) {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }

