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
            case "instagramFeed":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
                
                if($valid!=null)
                {
                    $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                    $userId=get_id($data->user_id);
                    $res->data = postlist();
                    
                    if($res->data==null)
                    {
                        $res->result=false;
                        $res->code=401;
                        $res->message="새로운 피드 없음";
                    }
                    else
                    {
                        $res->result=true;
                        $res->code = 100;
                        $res->message = "성공";
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
            * API No. 1
            * API Name : 팔로잉 리스트 API
            * 마지막 수정 날짜 : 18.02.01
            */

            case "following":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
                
                if($valid!=null)
                {
                    $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                    $res->result=followingList($data->user_id);
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
                case "follower":
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
            if($id==NULL||$name==NULL||$password==NULL||$email==NULL)
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
            else if(!emailCheck($email))
            {
                $res->result=false;
                $res->code=403;
                $res->message="중복되는 이메일 존재";
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

        case "follow":
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

        case "posts":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
            if($valid!=false)
            {
                $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                if($req->content==null||$req->url==null)
                {
                    $res->result=false;
                    $res->code=401;
                    $res->message="모두 입력해주세요";
                }
                else
                {
                    $data = getDataByJWToken($jwt,JWT_SECRET_KEY);
                    $res->result=write_action($req,$data);
                    if($res->result==true)
                    {
                        $res->code=100;
                        $res->message="글쓰기를 성공했습니다";
                    }
                    else
                    {
                        $res->code=400;
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
            $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
          
            if($valid!=false)
            {
                if($req->comment_content==null)
                {
                    $res->result=false;
                    $res->code=400;
                    $res->message="댓글 작성 실패";
                }
                else
                {
                    $post_number=$req->post_number;
                    $exist=POSTcheck($post_number);
                    if($exist)
                    {
                        $data = getDataByJWToken($jwt,'JWT_SECRET_KEY');
                        $res->result=create_comment($req,$data->user_id);  
                        $res->code=100;
                        $res->message="댓글 작성 완료";
                    }
                    else
                    {
                        $res->result=false;
                        $res->code=400;
                        $res->message="존재하지 않는 게시물임";
                    }
                    
                }
            }
            else
            {
                $res->code=401;
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
            * API Name : 프로필수정 API
            * 마지막 수정 날짜 : 18.02.01
            */

            case "profile":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
                if($valid!=null)
                {
                    if($req->name==NULL||$req->introduction==NULL||$req->image==NULL)
                    {
                        $res->result=false;
                        $res->code=400;
                        $res->message="수정 실패";
                    }
                    $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                    $res->result=changeProfile($req,$data);
                    if($res->result!=null)
                    {
                        $res->code=100;
                        $res->message="수정 완료";
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


            /*
            * API No. 11
            * API Name : 파일업로드 API
            * 마지막 수정 날짜 : 18.02.06
            */

                case "image":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
                
                if($valid!=null)
                {
                    $success=fileUpload();
                    if($success)
                   {
                        $res->result=$success;
                        $res->code=100;
                        $res->message="파일 업로드 성공";
                    }
                    else
                    {
                        $res->result=false;
                        $res->code=400;
                        $res->message="파일 업로드 실패";
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
            * API No. 12
            * API Name : 좋아요 API
            * 마지막 수정 날짜 : 18.02.06
            */

                case "likes":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');

                if($valid!=null)
                {
                    $postNumber=$req->post_number; 
                    $data=getDataByJWToken($jwt,'JWT_SECRET_KEY');
                    $check=likeCheck($postNumber,$data->user_id);
                    if(!$check)
                    {
                        $like=likeNum($postNumber);
                        $likePlus=$like+1;
                        $success=likes($postNumber,$likePlus);
                        if($success)
                        {
                            upLike($postNumber,$data->user_id);
                            $res->result=$success;
                            $res->code=100;
                            $res->message="좋아요 성공";
                        }
                        else
                        {
                            $res->result=false;
                            $res->code=400;
                            $res->message="좋아요 실패";
                        }
                    }
                    else
                        {
                            $res->result=false;
                            $res->code=400;
                            $res->message="좋아요 실패";
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
            * API No. 13
            * API Name : 유저정보 조회 API
            * 마지막 수정 날짜 : 18.02.06
            */
            case "userInfo":
                http_response_code(200);
                $userId=$vars["user_id"];
            
                if($res->data=userInfo($userId))
                {
                    $res->result=true;
                    $res->code=100;
                    $res->message="불러오기 성공";
                }
                else
                {
                    $res->result=false;
                    $res->code=400;
                    $res->message="불러오기 실패";
                }
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;



            /*
            * API No. 14
            * API Name : 게시글 수정 API
            * 마지막 수정 날짜 : 18.02.06
            */

            case "post":
                http_response_code(200);
                $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
                $valid=isValidHeader($jwt,'JWT_SECRET_KEY');

                if($valid!=null)
                {

                    if($req->content==NULL)
                    {
                        $res->result=false;
                        $res->code=400;
                        $res->message="내용을 입력해주세요";
                    }
                    else
                    {
                        $data = getDataByJWToken($jwt,'JWT_SECRET_KEY');
                        $post_number=$req->post_number;
                        $content=$req->content;
                        $exist=POSTcheck($post_number);
                        $qualify=writerCheck($post_number);
                        $id=get_id($data->user_id);
                        if($qualify==$id)
                        {
                            if($exist)
                            {
                                
                                $res->result=changeContent($content,$post_number);  
                                $res->code=100;
                                $res->message="수정 완료";
                            }
                            else
                            {
                                $res->result=false;
                                $res->code=402;
                                $res->message="존재하지 않는 게시물임";
                            }
                        }
                        else
                        {
                            $res->result=false;
                            $res->code=403;
                            $res->message="권한이 없습니다";
                        }
                    }
                }
                else
                {
                    $res->result=false;
                    $res->code=401;
                    $res->message="로그인 안함";
                }
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;


            /*
            * API No. 15
            * API Name : 프로필 조회 API
            * 마지막 수정 날짜 : 18.02.06
            */
            case "profilePage":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
            if($valid!=null)
            {
                $userId=$vars["user_id"];

                if($res->user=userInfo($userId))
                {
                    $res->post=profile($userId);
                    $res->result=true;
                    $res->code=100;
                    $res->message="조회 성공";
                }
                else
                {
                    $res->result=false;
                    $res->code=400;
                    $res->message="조회 실패";
                }
            }
            else
            {
                $res->result=false;
                $res->code=401;
                $res->message="로그인 안함";
            }
            echo json_encode($res,JSON_NUMERIC_CHECK);
            break;



            /*  
            * API No. 16
            * API Name : 게시물 삭제 API
            * 마지막 수정 날짜 : 18.02.07
            */
            case "Post":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $valid=isValidHeader($jwt,'JWT_SECRET_KEY');
          
            if($valid!=false)
            {
                if($req->post_number==null)
                {
                    $res->result=false;
                    $res->code=400;
                    $res->message="게시물 삭제 실패";
                }
                else
                {
                    $data = getDataByJWToken($jwt,'JWT_SECRET_KEY');
                    $post_number=$req->post_number;
                    $exist=POSTcheck($post_number);
                    $qualify=writerCheck($post_number);
                    $id=get_id($data->user_id);
                    if($exist)
                    {
                            if($qualify==$id)
                            {
                                
                                $res->result=delete_post($post_number);  
                                $res->code=100;
                                $res->message="삭제 완료";
                            }
                            else
                            {
                                $res->result=false;
                                $res->code=403;
                                $res->message="권한이 없습니다";
                               
                            }
                    }
                    else
                    {
                        $res->result=false;
                        $res->code=402;
                        $res->message="존재하지 않는 게시물임";
                    }
                }
            }
            else
            {
                $res->code=401;
                $res->message="로그인을 먼저 해주세요";
            }
            echo json_encode($res,JSON_NUMERIC_CHECK);
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
  


