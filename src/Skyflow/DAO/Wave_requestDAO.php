<?php

/**
 * DAO class for the Wave_request domain object.
 *
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace skyflow\DAO;

use skyflow\Domain\Wave_request;

/**
 * DAO class for the Users domain object.
 */
class Wave_requestDAO extends DAO {

    /**
     * Find all Wave requests owned by a User from the User's id.
     *
     * @param string $id_user The User id.
     * @return Wave_request[] An array of found Wave_request. Empty array if none found.
     */
    public function findAllByUser($id_user){
        $sql = "select * from wave_request where id_user =? limit 10";
        $request = $this->getDb()->fetchAll($sql,array($id_user));

        $requests = array();
        foreach ($request as $row) {
            $requestId = $row['id'];
            $requests[$requestId] = $this->buildDomainObject($row);
        }

        return $requests;
    }

    /**
     * Find a Wave request domain object from the request string.
     *
     * @param string $request The request string.
     * @param string $id_user The id of the User who owns the Wave_request.
     * @return Wave_request|null The found Wave_request or null if none found.
     */
    public function findByRequest($request,$id_user){

        $sql = $this->getDb()->prepare("select * from wave_request where request = ? and id_user =?");
        $sql->bindValue(1,$request);
        $sql->bindValue(2,$id_user);
        $sql->execute();
        $result = $sql->fetch();

        if($result){
            return $this->buildDomainObject($result);
        }
    }

    /**
     * Find a Wave request by its id.
     *
     * @param string $id The Wave request id.
     * @return Wave_request|null The found Wave request or null if none found.
     */
    public function findById($id){
        $sql = $this->getDb()->prepare("select * from wave_request where id = ?");
        $sql->bindValue(1,$id);
        $sql->execute();
        $request = $sql->fetch();

        if($request){
            return $this->buildDomainObject($request);
        }
    }

    /**
     * Save a Wave request domain object.
     *
     * @param Wave_request $waverequest The Wave request domain object to save.
     */
    public function save(Wave_request $waverequest){
        $wave_requestData = array (
            'request' => $waverequest->getRequest(),
            'id_user' => $waverequest->getIdUser(),
        );

        if($waverequest->getId()){
            $this->getDb()->update('wave_request',$wave_requestData,array('id'=>$waverequest->getId()));
        }else{
            $this->getDb()->insert('wave_request',$wave_requestData);
            $id = $this->getDb()->lastInsertId();
            $waverequest->setId($id);
        }
    }

    /**
     * Creates a Wave_request object based on a DB row.
     *
     * @param array $row The DB row containing Wave_request data.
     * @return Wave_request
     */
    protected function buildDomainObject($row) {
        $waveRequest = new Wave_request();
        $waveRequest->setId($row['id']);
        $waveRequest->setRequest($row['request']);
        $waveRequest->setIdUser($row['id_user']);
        return $waveRequest;
    }
}