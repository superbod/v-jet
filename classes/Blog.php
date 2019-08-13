<?php

require_once("{$_SERVER['DOCUMENT_ROOT']}../../classes/Base.php");

class Blog extends Base
{

    public function __construct($db_config)
    {
        parent::__construct($db_config);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getData($id = 0){
        $res = [];
        $where = "";
        $bindValue = [];
        $needlessID = $this->getTopCommentsIDs(5);

        if($id > 0) {
            $where = " WHERE b.id = :id";
            $bindValue = ["id" => $id];
        } elseif (!empty($needlessID)) {
            $inKeys = implode(',', $needlessID);
            $where = " WHERE b.id NOT IN ({$inKeys})";
        }

        $sql = "SELECT b.id, b.text, b.date, b.author_name, count(c.id) as comment_numbers, b.name
                FROM blog as b
                lEFT JOIN comments as c
	                ON c.blogID = b.id
                {$where}
	            GROUP BY b.id";

        $stm = $this->pdo->prepare("{$sql}");

        if(!empty($bindValue)) {
            foreach ($bindValue as $key=>$value) {
                $stm->bindValue($key, $value);
            }
        }
        $stm->execute();
        while($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $res[] = $row;
        }
        return $res;
    }

    /**
     * @param int $number
     * @return array
     */
    private function getTopCommentsIDs($number) {
        $blogIDs = [];
        $sql = "SELECT  c.blogID 
                  FROM comments as c
                  GROUP BY c.blogID
                  ORDER BY count(c.blogID) DESC
                  LIMIT {$number}";

        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        while ($row = $stm->fetch(PDO::FETCH_NUM)) {
            $blogIDs[] = intval($row[0]);
        }
        return $blogIDs;
    }

    /**
     * @param int $number
     * @return array
     */
    public function getSliderPosts($number) {
        $res = [];
        $blogIDs = $this->getTopCommentsIDs($number);
        $inKeys = implode(',',$blogIDs);
        $stm = $this->pdo->prepare("SELECT * 
                                        FROM blog as b
                                        WHERE b.id IN ({$inKeys})");
        $stm->execute();
        while ($row = $stm->fetch(PDO::FETCH_OBJ)) {
            $res[] = $row;
        }

        return $res;
    }

    /**
     * @param $args
     * @return array
     */
    public function createPost($args) {
        $sql = "INSERT INTO blog (text, author_name, name) VALUES (:text, :author_name, :name)";
        $stm = $this->pdo->prepare($sql);
        $stm->bindValue("text", $args['text']);
        $stm->bindValue("author_name", $args['author_name']);
        $stm->bindValue("name", $args['name']);
        $stm->execute();
        $newPostId = $this->pdo->lastInsertId();
        $res = $this->getData($newPostId);

        return $res;
    }

    /**
     * @param $postID
     * @return array
     */
    public function getComments($postID) {
        $res = [];
        $sql = "SELECT * FROM comments as c WHERE c.blogID = :postID";
        $stm = $this->pdo->prepare($sql);
        $stm->bindValue("postID", $postID);
        $stm->execute();
        while($row = $stm->fetch(PDO::FETCH_OBJ)) {
            $res[] = $row;
        }

        return $res;
    }

    /**
     * @param $postID
     * @return array
     */
    public function getPost($postID) {
        $res['post'] = $this->getData($postID);
        $res['comments'] = $this->getComments($postID);
        return $res;
    }

    /**
     * @param $args
     * @return array
     */
    public function createComment($args) {
        $sql = "INSERT INTO comments (comment, author_name, blogID) VALUES (:comment, :author_name, :blogID)";
        $stm = $this->pdo->prepare($sql);
        $stm->bindValue("comment", $args['comment']);
        $stm->bindValue("author_name", $args['author_name']);
        $stm->bindValue("blogID", $args['blogID']);
        $stm->execute();
        $newPostId = $this->pdo->lastInsertId();
        $res = $this->getCommentByID($newPostId);

        return $res;
    }

    public function getCommentByID($commentID) {
        $sql = "SELECT * FROM comments as c WHERE c.id = :id";
        $stm = $this->pdo->prepare($sql);
        $stm->bindValue("id", $commentID);
        $stm->execute();
        return $stm->fetch(PDO::FETCH_OBJ);

    }
}