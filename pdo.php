<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

    function postlist()
    {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM QnA order by date desc;";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }
    function users()
    {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM user WHERE current='0'";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }
    function follower($my_id,$friend_id) //?”ë¡œ?‰ì— ?†ëŠ”? ë©´ ?”ë¡œ???”ì²­
    {
        $pdo=pdoSqlConnect();
        $query="insert into follower (my_id,follower) values('$friend_id','$my_id')";
        echo $query;
        $st=$pdo->prepare($query);
        $st->execute();
        $st=null;$pdo=null;
    }
    function following($my_id,$friend_id) //?”ë¡œ?Œì— ?ˆëŠ”? ë©´ ë§íŒ”ë¡œìš°
    {
        $pdo=pdoSqlConnect();
        $query="insert into following (my_id,following) values('$my_id','$friend_id')";
        echo $query;
        $st=$pdo->prepare($query);
        $st->execute();
        $res=$st;
        $st=null;$pdo=null;

        if($res!=null)
        {
            follower($my_id,$friend_id);
            return true;
        }
        else
        {
            return false;
        }
    }
    function get_id($phone)
    {
        $pdo=pdoSqlConnect();
        $query="select user_id from user where phone='$phone'";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();
        $st=null;$pdo = null;
        
        return $res[0]['user_id'];
    }
    function following_list($data)
    {
        $pdo=pdoSqlConnect();
        $phone= $data->user_id;
        $user_id=get_id($phone);
        $query="select following from following where my_id='$user_id'";
        echo $query;
        $st=$pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }
    function isValidUser($user)
    {
	    $pdo=pdosqlConnect();
	    $query="SELECT EXISTS(SELECT * FROM user WHERE phone = ? AND user_password = ?) as result";
        $st = $pdo->prepare($query);
	    $st->execute([$user->user_id, $user->user_password]);
	    $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();
        
        $st=null; $pdo=null;
        echo $res[0]["result"];
	    return intval($res[0]["result"]);	
    }
    function view_comment($questionNumber)
    {
	    $pdo=pdosqlConnect();
	    $query="select * from QnA left outer join comment on QnA.question_number = comment.question_number where QnA.question_number= '$questionNumber'";
       
        $st = $pdo->prepare($query);
	    $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();
        $st=null;$pdo = null;

        return $res;
    }
    function view_recomment($questionNumber)
    {
        $pdo=pdosqlConnect();
        $query="select * from comment left outer join recomment on comment.comment_number =recomment.comment_number where comment.question_number='$questionNumber'";
        $st = $pdo->prepare($query);
	    $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();
        $st=null;$pdo = null;
        return $res;
    }
    function IDcheck($id)
    {
        $pdo=pdosqlConnect();
	    $query="select * from user where user_id='$id'";
	    $st = $pdo->prepare($query);
	    $st->execute();
	    $st->setFetchMode(PDO::FETCH_ASSOC);
	    $res = $st->fetchAll();
        $st=null;$pdo = null;

        if($res !=null)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    function user_PWcheck($userID,$password)
    {
        $pdo=pdosqlConnect();
	    $query="select * from user where userID='$userID' and user_password='$password'";
	    $st = $pdo->prepare($query);
	    $st->execute();
	    $st->setFetchMode(PDO::FETCH_ASSOC);
	    $res = $st->fetchAll();
        $st=null;$pdo = null;

        if($res !=null)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    function POSTcheck($question_number)
    {
        $pdo=pdosqlConnect();
	    $query="select * from QnA where question_number='$question_number'";
	    $st = $pdo->prepare($query);
	    $st->execute();
	    $st->setFetchMode(PDO::FETCH_ASSOC);
	    $res = $st->fetchAll();
        $st=null;$pdo = null;

        if($res !=null)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    function CURRENTcheck($userID)
    {
        $pdo=pdosqlConnect();
	    $query="select * from user where userID='$userID' and current=1";
	    $st = $pdo->prepare($query);
	    $st->execute();
	    $st->setFetchMode(PDO::FETCH_ASSOC);
	    $res = $st->fetchAll();
        $st=null;$pdo = null;

        if($res !=null)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
   
    function createUser($req)
    {
        $pdo=pdoSqlConnect();
        $id=$req->id;
        $password=$req->password;
        $name=$req->name;
        $email=$req->email;
        $phone=$req->phone_number;
        
        $check=IDcheck($id);

        if(!$check)
        {
            return false;
        }
        else
        {
                $sql="insert into user (user_id,user_password,name,email,phone) values ('$id','$password','$name','$email','$phone')";
                $sr=$pdo->prepare($sql);
                $sr->execute();
                $sr=null;$pdo = null;

                return true;
        }
    }
    function write_action($req,$data)
    {
        $pdo=pdosqlConnect();

        $content=$req->content;
        $id=$data->userID;
        $password=$req->password;

        $query="insert into QnA (question_number,content,id,password) values (null,'$content','$id','$password')";
        $st = $pdo->prepare($query);
	    $st->execute();
	    $res = $st;
        $st=null;
        $pdo = null;

        if($res!=null)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    function create_comment($req,$data)
    {
        $pdo=pdosqlConnect();

        $comment_content=$req->comment_content;
        $question_number=$req->question_number;
        $commentid=$data->userID;
        $exist=POSTcheck($question_number);

        if(strlen($comment_content)==0)
        {
            return "NO_CONTENT";
        }

        if($exist==true)
        {
            $query="insert into comment (question_number,comment_content,commentid,comment_number) values ('$question_number','$comment_content','$commentid',null)";
            $st = $pdo->prepare($query);
            $st->execute();
            $res = $st;
            $st=null;
            $pdo = null;

            if($res!=null)
            {
                return "true";
            }
            else
            {
                return "false";
            }
        }
        else
        {
            return "NO_POST";
        } 
    }
    function create_recomment($req,$data)
    {
        $pdo=pdosqlConnect();

        $recomment_content=$req->recomment_content;
        $comment_number=$req->comment_number;
        $question_number=$req->question_number;
        $recommentid=$data->userID;

        $exist=POSTcheck($question_number);
    
        if($exist==true)
        {
            $query="insert into recomment (comment_number,recomment_content,recomment_id) values ('$comment_number','$recomment_content','$recommentid')";
            echo $query;
            $st = $pdo->prepare($query);
            $st->execute();
            $res = $st;
            $st=null;
            $pdo = null;

            if($res!=null)
            {
                return "true";
            }
            else
            {
                return "false";
            }
        }
        else
        {
            return "NO_POST";
        }
    }
    function removeUser($req)
    {
        $pdo=pdosqlConnect();
        $userID=$req->userID;
        $user_password=$req->user_password;

        $query="update user SET current='1' where userID='$userID' and user_password='$user_password' and current='0'";
        $st = $pdo->prepare($query);
	    $st->execute();
	    $res = $st;
        $st=null;
        $pdo = null;

        if($res!=null)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function reviveUser($req)
    {
        $pdo=pdosqlConnect();
        $userID=$req->userID;
        $user_password=$req->user_password;

        $exist = IDcheck($userID);
        printf($exist);
        $check = user_PWcheck($userID,$user_password);

        if($exist)
        {   
            return;
        }
        else
        {
            if(!$check)
            {
                if(CURRENTcheck($userID))
               {
                    $query="UPDATE user set current=0 where current=1 and userID='$userID' and user_password='$user_password'";
                    $st = $pdo->prepare($query);
                    $st->execute();
                    $res = $st;
                    $st=null;
                    $pdo = null;

                    if($res!=null)
                    {
                        return "true";
                    }
                    else
                    {
                        return "false";
                    }
                }   
                else
                {
                    return "false";
                }
            }
            else
            {
                return "false";
            }
        }
        
    }

