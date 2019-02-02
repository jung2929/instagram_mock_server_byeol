<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
    require 'function.php';
    $res = (Object)Array();
    header('Content-Type: json');
    $req = json_decode(file_get_contents("php://input"));
//    $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];

    try {
        addAccessLogs($accessLogs, $req);
        switch ($handler) {
            case "index":
                echo "API Server";
                break;

//        echo phpinfo();

            case "ACCESS_LOGS":
//            header('content-type text/html charset=utf-8');
                header('Content-Type: text/html; charset=UTF-8');

                getLogs("./logs/access.log");
                break;
            case "ERROR_LOGS":
//            header('content-type text/html charset=utf-8');
                header('Content-Type: text/html; charset=UTF-8');

                getLogs("./logs/errors.log");
                break;
            /*
            * API No. 0
            * API Name : 피드 조회 API
            * 마지막 수정 날짜 : 18.02.01
            */
            case "timeline":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $res->result = postlist();
                $res->code = 100;
                $res->message = "성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
		        break;

            /*
            * API No. 1
            * API Name : 팔로잉 리스트 API
            * 마지막 수정 날짜 : 18.02.01
            */

            case "followingList":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
                
                if($valid!=null)
                {
                    $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                    $res->result=following_list($data->user_id);
                    if($res->result!=null)
                    {
                        $res->code=100;
                        $res->message="불러오기 성공";
                    }
                    else
                    {
                        $res->result=false;
                        $res->code=400;
                        $res->message="팔로잉 없음";
                    }
                }
                else
                {
                    $res->result=false;
                    $res->code=401;
                    $res->message="로그인 필요";
                }
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;




            /*
            * API No. 2
            * API Name : 팔로워 리스트 API
            * 마지막 수정 날짜 : 18.02.01
            */
                case "follower_list":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
                
                
                if($valid)
                {
                    $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                    $res->result=followerList($data->user_id);
                    if($res->result!=null)
                    {
                        $res->code=100;
                        $res->message="불러오기 성공";
                    }
                    else
                    {
                        $res->result=false;
                        $res->code=400;
                        $res->message="불러오기 실패";
                    }
                }
                else
                {
                    $res->result=false;
                    $res->code=401;
                    $res->message="로그인 필요";
                }
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 3
            * API Name :  API
            * 마지막 수정 날짜 : 18.02.01
            */

	        case "view":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
		    $questionNumber=$vars["questionNumber"];
            $res->comment = view_comment($questionNumber);
            $res->recomment = view_recomment($questionNumber);

            
            if(POSTcheck($questionNumber) != false)
            {
                $res->code = 100;
                $res->message ="글 불러오기";
            }
            else
            {
                $res->code = 400;
                $res->message ="존재하지 않는 게시글입니다";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            
            
            /*
            * API No. 4
            * API Name : 로그인 API
            * 마지막 수정 날짜 : 18.02.01
            */


	    case "login": 
		    http_response_code(200);
            $valid = isValidUser($req);

            $id=$req->user_id;
            $password=$req->user_password;
          
            if($valid)
           { 
                $res->result=getJWToken($id,$password,'JWT_SECRET_KEY');
                $res->code=100;
                $res->message = "로그인에 성공하였습니다";
               
           }
           else
            {
                $res->result=false;
                $res->code=400;
                $res->message="로그인에 실패했습니다";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        


             /*
            * API No. 5
            * API Name : 회원가입 API
            * 마지막 수정 날짜 : 18.02.01
            */

        case "User":
            http_response_code(200);

            $id=$req->id;
            $password=$req->password;
            $name=$req->name;
            $email=$req->email;
            $introduction=$req->introduction;

            if($id==NULL||$name==NULL||$password==NULL||$email==null)
            {
                $res->result=false;
                $res->code=400;
                $res->message="내용을 모두 입력해주세요";
            }
            else if(strlen($id)>15||strlen($id)<4)
            {
                $res->result=false;
                $res->code=401;
               $res->message="아이디는 4자리 이상 15자리 이하여야합니다";
            }
            else if(strlen($password)<8||strlen($password)>20)
            {
                $res->result=false;
                $res->code=402;
                $res->message="비밀번호는 8자리이상 20자리 이하여야합니다";
            }
            else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $res->result=false;
                $res->code=403;
                $res->message="이메일 형식이 잘못되었습니다";
            }
            else
            {
                $res->result = createUser($req);
                if($res->result==false)
                {
                    $res->code=401;
                    $res->message="중복되는 아이디 존재";
                }
                else
                {
                    $res->code=100;
                    $res->message="회원가입에 성공했습니다";
                }
            }
            echo json_encode($res,JSON_NUMERIC_CHECK);
            break;


            /*
            * API No. 6
            * API Name : 팔로잉 API
            * 마지막 수정 날짜 : 18.02.01
            */

        case "following":
            http_response_code(200);

            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];

            $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
            
            if($valid!=false)
            {
                $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                $my_id=get_id($data->user_id);
                $friend_id=$req->friend_id;
                $res->result=following($my_id,$friend_id);
                
                if($res->result!=false)
                {
                    $res->code=100;
                    $res->message="팔로우 성공";
                }
                else
                {
                    $res->result=false;
                    $res->code=400;
                    $res->message="팔로우 실패";
                }

            }
            else
            {
                $res->result=false;
                $res->code=401;
                $res->message="로그인 필요";
                }
            echo json_encode($res,JSON_NUMERIC_CHECK);
            break;
            
            /*
            * API No. 7
            * API Name : 게시글 작성 API
            * 마지막 수정 날짜 : 18.02.01
            */

        case "write":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $valid=isValidHeader($jwt,JWT_SECRET_KEY);
            if($valid!=false)
            {
                if($req->content==null)
                {
                    $res->result=false;
                    $res->code=401;
                    $res->message="글 내용을 입력해주세요";
                }
                else if($req->password==null)
                {
                    $res->result=false;
                    $res->code=403;
                    $res->message="글 비밀번호를 입력해주세요";
                }
                else
                {
                    $data = getDataByJWToken($jwt,JWT_SECRET_KEY);
                    $res->result=write_action($req,$data);  
                    if($res->result==true) 
                    {
                        $res->code=101;
                        $res->message="글쓰기를 성공했습니다";
                    }
                    else
                    {
                        $res->code=404;
                        $res->message="글쓰기 실패";
                    }
                }
            }
            else
            {
                $res->code=402;
                $res->message="로그인을 먼저 해주세요";
            }
            echo json_encode($res,JSON_NUMERIC_CHECK);
            break;



             /*
            * API No. 8
            * API Name : 댓글작성 API
            * 마지막 수정 날짜 : 18.02.01
            */
        case "comment":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $valid=isValidHeader($jwt,JWT_SECRET_KEY);
          
            if($valid!=false)
            {
                $data = getDataByJWToken($jwt,JWT_SECRET_KEY);

                $res->result=create_comment($req,$data);  

                switch($res->result)
                {
                    case "true":
                    {
                    $res->code=102;
                    $res->message="댓글 작성을 성공했습니다";
                    break;
                    }
                    case "false":
                    {
                    $res->code=405;
                    $res->message="댓글 작성을 실패했습니다";
                    break;
                    }
                    case "NO_POST":
                    {
                    $res->code=406;
                    $res->message="존재하지 않는 게시글입니다";
                    break;
                    }
                    case "NO_CONTENT":
                    {
                        $res->code=407;
                        $res->message="내용을 입력해주세요";
                    }
                }
            }
            else
            {
                $res->code=402;
                $res->message="로그인을 먼저 해주세요";
            }
            echo json_encode($res,JSON_NUMERIC_CHECK);
            break;

             /*
            * API No. 9
            * API Name : 대댓글작성 API
            * 마지막 수정 날짜 : 18.02.01
            */
        case "recomment":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $valid=isValidHeader($jwt,JWT_SECRET_KEY);
          
           
            if($valid!=false)
            {
                $data = getDataByJWToken($jwt,JWT_SECRET_KEY);
                
                $res->result=create_recomment($req,$data);  

                switch($res->result)
                {
                    case "true":
                    {
                        $res->code=102;
                        $res->message="댓글 작성을 성공했습니다";
                        break;
                    }
                    case "false":
                    {
                        $res->code=405;
                        $res->message="댓글 작성을 실패했습니다";
                        break;
                    }
                    case "NO_POST":
                    {
                        $res->code=406;
                        $res->message="존재하지 않는 게시글입니다";
                        break;
                    }
                    case "NO_CONTENT":
                    {
                        $res->code=407;
                        $res->message="내용을 입력해주세요";
                        break;
                    }
                    }
            }
            else
            {
                $res->code=402;
                $res->message="로그인을 먼저 해주세요";
            }   
            echo json_encode($res,JSON_NUMERIC_CHECK);
            break;

            /*
            * API No. 10
            * API Name : 프로필조회 API
            * 마지막 수정 날짜 : 18.02.01
            */

            case "myprofile":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,JWT_SECRET_KEY);
                

                if($valid!=null)
                {
                    $data=getDataByJWToken($jwt,JWT_SECRET_KEY);
                    $res->result=profile_fix($data,$req);

                    if($res->result!=null)
                    {
                        $res->code=100;
                        $res->message="수정 성공";
                    }
                    else
                    {
                        $res->result=false;
                        $res->code=400;
                        $res->message="수정 실패";
                    }
                }
                else
                {
                    $res->result=false;
                    $res->code=401;
                    $res->message="로그인 필요";
                }
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
        

/*        case "removeUser":
                http_response_code(200);
                $valid=isValidHeader($jwt,JWT_SECRET_KEY);

                if($valid!=false)
                {
                    $data = getDataByJWToken($jwt,JWT_SECRET_KEY);
                    $res->result=removeUser($req);  
                    if($res->result==true) 
                    {
                        $res->code=102;
                        $res->message="계정 비활성화 완료";
                    }
                    else
                    {
                        $res->code=405;
                        $res->message="계정 비활성화 실패";
                    }
                }
                else
                {
                    $res->code=402;
                    $res->message="로그인을 먼저 해주세요";
                }
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;


            case "reviveUser":
                http_response_code(200);

                $res->result=reviveUser($req);  

                switch($res->result) 
                {
                    case "true":
                    {
                        $res->code=102;
                        $res->message="계정 활성화 완료";
                        break;
                    }
                    case null:
                    {
                        $res->code=405;
                        $res->message="존재하지 않는 계정입니다";
                        break;
                    }
                    case "false":
                    {
                        $res->code=406;
                        $res->message="계정 활성화 실패";
                        break;
                    }
                }
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;

                case "users":
                http_response_code(200);
                $res->result = users();
                $res->code = 100;
                $res->message = "성공";

                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
                */

            }

    }
    catch (Exception $e) 
    {
	    return getSQLErrorException($errorLogs, $e, $req);
    }
  


