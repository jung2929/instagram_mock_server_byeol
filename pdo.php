<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
    function pdoSqlConnect()
    {
        try 
        {        }
         catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }
    function postlist
{
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM post order by date desc limit 10;";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }
    function userInfo($userId)
    {
        $pdo = pdoSqlConnect();
        $query = "select name,user_id,introduction,profileImage from user where user_id = ? ";
        $st = $pdo->prepare($query);
        $st->execute([$userId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }
    function followingCheck($user_id,$friend_id)
    {
        $pdo=pdosqlConnect();
        $query="select exists(select * from following where my_id = ? and following = ? )as result";
        $st = $pdo->prepare($query);
        $st->execute([$user_id,$friend_id]);
	    $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $st=null;$pdo=null;
    
	    return intval($res[0]["result"]);
    }
    function get_id($email)
    {
        $pdo=pdosqlConnect();
	    $query="select user_id from user where email = ? ";
	    $st = $pdo->prepare($query);
	    $st->execute([$email]);
	    $st->setFetchMode(PDO::FETCH_ASSOC);
	    $res = $st->fetchAll();
        $st=null;$pdo = null;
        return $res[0]['user_id'];
    }
    function following($my_id,$friend_id)
    {
        if(!followingCheck($my_id,$friend_id))
        {
            $pdo=pdoSqlConnect();
            $query="insert into following (my_id,following) values(?,?)";
            $st=$pdo->prepare($query);
            $st->execute([$my_id,$friend_id]);
            $res=$st;
            $st=null;$pdo=null;

            if($res!=null)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;    
        }
        
    }
    
    function followingList($data)
    {
        $pdo=pdoSqlConnect();
        $user_id=get_id($data);
        $query="select following from following where my_id= ?";
        $st=$pdo->prepare($query);
        $st->execute([$user_id]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }
    function followerList($data)
    {
        $pdo=pdoSqlConnect();
        $user_id=get_id($data);
        $query="select my_id from following where following= ? ";
        $st=$pdo->prepare($query);
        $st->execute([$user_id]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();
        $st=null;$pdo = null;

        return $res;
    }
    function isValidUser($user)
    {
        $pdo=pdosqlConnect();
        $query="SELECT EXISTS(SELECT * FROM user WHERE email = ? AND user_password = ?) as result";
        $st = $pdo->prepare($query);
        $st->execute([$user->user_id, $user->user_password]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res=$st->fetchAll();
        $st=null; $pdo=null;
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
   
    function IDcheck($id)
    {
        $pdo=pdosqlConnect();
	    $query="select * from user where user_id= ? ";
	    $st = $pdo->prepare($query);
	    $st->execute([$id]);
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
    function emailCheck($email)
    {
        $pdo=pdosqlConnect();
	    $query="select * from user where email= ? ";
	    $st = $pdo->prepare($query);
	    $st->execute([$email]);
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
	    $query="select * from post where postNumber='$question_number'";
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
        $introduction=$req->introduction;

        $check=IDcheck($id);

            if(!$check)
            {
                return false;
            }
            else
            {
                $sql="insert into user (user_id,user_password,name,email,introduction) values ( ? , ? , ? , ? ,? )";
                $sr=$pdo->prepare($sql);
                $sr->execute([$id,$password,$name,$email,$introduction]);
                $sr=null;$pdo = null;
                return true;
            }
    }
    function write_action($req,$data)
    {
        $pdo=pdosqlConnect();

        $content=$req->content;
        $URL=$req->url;
        $id=get_id($data->user_id);
        $query="insert into post (content,writer,picture) values ( ? , ? , ? )";
        $st = $pdo->prepare($query);
	    $st->execute([$content,$id,$URL]);
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
    function create_comment($req,$user_id)
    {
        $pdo=pdosqlConnect();
        $commentContent=$req->comment_content;
        $postNumber=$req->post_number;
        $commentId=get_id($user_id);

            $query="insert into comment (postNumber,commentContent,commentId) values (?,?,?)";
            $st = $pdo->prepare($query);
            $st->execute([$postNumber,$commentContent,$commentId]);
            $res = $st;
            $st=null;$pdo = null;

            if($res!=null)
            {
                return true;
            }
            else
            {
                return false;
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
    function URL($url)
    {
        $pdo=pdosqlConnect();
	    $query="insert into post where ";
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
    function likeNum($postNumber)
    {
        $pdo=pdosqlConnect();
	    $query="select likes from post where postNumber= ? ";
	    $st = $pdo->prepare($query);
	    $st->execute([$postNumber]);
	    $st->setFetchMode(PDO::FETCH_ASSOC);
	    $res = $st->fetchAll();
        $st=null;$pdo = null;

        return $res[0]['likes'];
    }
    function likes($postNumber,$like)
    {
        $pdo=pdosqlConnect();
        $exist=POSTcheck($postNumber);
           if($exist==true)
        {
            $query="UPDATE post set likes=$like where postNumber = ? ";
            $st = $pdo->prepare($query);
            $st->execute([$postNumber]);
            $res = $st;
            $st=null;$pdo = null;

            if($res!=null)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        } 
    }

    function writerCheck($postNumber)
    {
        $pdo=pdosqlConnect();
	    $query="select writer from post where postNumber= ? ";
	    $st = $pdo->prepare($query);
	    $st->execute([$postNumber]);
	    $st->setFetchMode(PDO::FETCH_ASSOC);
	    $res = $st->fetchAll();
        $st=null;$pdo = null;
        return $res[0]['writer'];
    }
  function changeContent($content,$postNumber)
  {
    $pdo=pdosqlConnect();
    $query="update post set content='$content' where postNumber='$postNumber'";
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
  function profile($userId)
  {
    $pdo=pdosqlConnect();
    $query="select * from post where writer = ?";
    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
	$res = $st->fetchAll();
    $st=null;
    $pdo = null;

    return $res;
  }

    /*
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
*/
 /*function users()
    {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM user WHERE current='0'";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }*/