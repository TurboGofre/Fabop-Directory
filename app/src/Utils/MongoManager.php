<?php

namespace App\Utils;

use MongoDB;
use App\Exception\DocumentNotFoundException;

class MongoManager
{

    private $client;
    private $db;
    private $doc;

    function __construct()
    {
        $this->client= new MongoDB\Client(getenv('MONGODB_URL'));
        $this->db= $this->client->fabop_directory;
    }

    function getDocById($collection,$id,$projection=[]){
        $this->doc=NULL;
        $collection= $this->db->selectCollection($collection);
        $this->doc= $collection->findOne(['_id'=>new MongoDB\BSON\ObjectId($id)],['projection'=>$projection]);
        if($this->doc==NULL){
            throw new DocumentNotFoundException;
        }else{
            //Mongo Object to JSON Object
            $this->doc= json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($this->doc)),true);
            return $this->doc;
        }
    }

    function getAllPermission($collection) {
        $this->doc=NULL;
        $collection= $this->db->selectCollection($collection);
        $this->doc= $collection->find(['label' => new MongoDB\BSON\Regex('.*')]);
        $res = array();
        foreach ($this->doc as $perm) {
          array_push($res, $perm);
        }
        if($this->doc==NULL){
            throw new DocumentNotFoundException;
        }else{
            return $res;
        }
    }

    function getDocByFilter($collection,$filters,$projection=NULL){
        $collection= $this->db->selectCollection($collection);
        $filterParam=[];
        foreach($filters as $filter=>$data){
            $filterParam[$filter]=['$in'=>$data];
        }
        $result= $collection->find($filterParam,['projection'=>$projection]);
        $this->doc=[];
        if($result==NULL){
            throw new DocumentNotFoundException;
        }else{
            //Results Array itteration
            foreach($result as $singledoc){
                //Mongo Object to JSON Object
                $this->doc[]= MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($singledoc));
            }
            return $this->doc;
        }
    }

    function insertSingle($collection,$data){
        $collection= $this->db->selectCollection($collection);
        return (string)$collection->insertOne($data)->getInsertedID(); // ex : 5c1bd8936e7dcf0149085eb2
    }

    function deleteSingleById($collection,$id){
        $collection= $this->db->selectCollection($collection);
        return $result_count=$collection->deleteOne(['_id'=>new MongoDB\BSON\ObjectId($id)])->getDeletedCount();
    }

    function updateSingleValueById($collection,$id,$key,$data){
        $collection= $this->db->selectCollection($collection);
        return $result_count=$collection->updateOne(['_id'=>new MongoDB\BSON\ObjectId($id)],['$set'=>[$key=>$data]])->getModifiedCount();
    }

    function updateSingleValueByJson($collection,$id,$json){
        $collection= $this->db->selectCollection($collection);
        return $result_count=$collection->updateOne(['_id'=>new MongoDB\BSON\ObjectId($id)],['$set'=>$json])->getModifiedCount();
    }

    function unsetSingleValueById($collection,$id,$key){
        $collection= $this->db->selectCollection($collection);
        $result_count=$collection->updateOne(['_id'=>new MongoDB\BSON\ObjectId($id)],['$unset'=>[$key=>""]])->getModifiedCount();
        if ($result_count>0){
            return $result_count;
        } else {
            throw new DocumentNotFoundException;
        }
    }

    // debug functions

    function var_dump(){
        echo "Mongo :";
        var_dump(MongoDB\BSON\toPHP(MongoDB\BSON\fromJSON($this->doc)));
        echo "PHP :";
        var_dump(json_decode($this->doc));
    }

    function debug(){
        var_dump($this->client);
        var_dump($collection);
        var_dump($this->doc);
    }

}
