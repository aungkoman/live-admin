<?php
class CHANNEL
{
    public function __construct($conn){
        // create database connection
        $this->conn = $conn;
    }
    private function xss_clean($string){
        $string = htmlentities(htmlspecialchars(stripslashes(trim($string))));
        return $string;
    }
    public function register($channel){
        // restrict user input data
        $id = (string) isset($channel['id']) ? $channel['id'] : null;
        if($id != null ){
            $this->update($channel);
        }
        $name = (string) isset($channel['name']) ? $channel['name'] : null;
        $poster = (string) isset($channel['poster']) ? $channel['poster'] : null;
        $url = (string) isset($channel['url']) ? $channel['url'] : null;
        $country = (int) isset($channel['country']) ? $channel['country'] : null;
        $created_date = date("Y-m-d");
        $modified_date = date("Y-m-d");

        $name = $this->xss_clean($name);
        // poster = url
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            
        } else {
            return_fail('CHANNEL register : invalid url',"invalid url");
        }
        $country = $this->xss_clean($country);

        $sql = "INSERT INTO `channel` (`name`, `poster`, `url`, `country`, `created_date`, `modified_date`) VALUES (?,?,?,?,?,?);";
        $stmt = $this->conn->prepare($sql);
        if ( false===$stmt ) {
            return_fail('prepare_failed CHANNEL register',htmlspecialchars($this->conn->error));
        }
        $rc = $stmt->bind_param("sssiss",
            $name,$poster,$url,$country,$created_date,$modified_date);
        if ( false===$rc ) {
            return_fail('bind_param_failed CHANNEL register',htmlspecialchars($stmt->error));
        }
        $rc = $stmt->execute();
        if ( false===$rc ) {
            return_fail('execute_failed CHANNEL register',htmlspecialchars($this->conn->errno).":".htmlspecialchars($stmt->error));
        }
        $insert_id = $this->conn->insert_id;
        $stmt->close();
        return_success(" CHANNEL register success",$insert_id);
    }

    public function select_all(){
        $sql = "SELECT channel.*,country.name as country_name FROM channel,country WHERE channel.country = country.id ORDER BY country.id";
        $stmt = $this->conn->prepare($sql);
        if ( false===$stmt ) {
            return_fail('prepare_failed CHANNEL select_all', htmlspecialchars($this->conn->error));
        }
        $rc = $stmt->execute();
        if ( false===$rc ) {
            return_fail('execute_failed:  CHANNEL select_all ',htmlspecialchars($this->conn->errno) ." : ". htmlspecialchars($stmt->error));
        }
        $result = $stmt->get_result();
        $affected_rows = $result->num_rows;
        if($affected_rows > 0 ){
            $data = $result->fetch_all(MYSQLI_ASSOC);
            return_success(" CHANNEL select_all success",$data);
        }else{
            return_fail(" CHANNEL select_all fail : no data");
        }
        $stmt->close();
    }

    private function get($channel_id){
        $sql = "SELECT * FROM channel WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ( false===$stmt ) {
        return_fail('prepare_failed CHANNEL get',htmlspecialchars($this->conn->error));
        }
        $rc = $stmt->bind_param("i",$channel_id);
        if ( false===$rc ) {
        return_fail('bind_param_failed  CHANNEL get', htmlspecialchars($stmt->error));
        }
        $rc = $stmt->execute();
        if ( false===$rc ) {
        return_fail('execute_failed CHANNEL get',htmlspecialchars($this->conn->errno) .":". htmlspecialchars($stmt->error));
        }
        $result = $stmt->get_result();
        $affected_rows = $result->num_rows;
        $stmt->close();
        if($affected_rows > 0 ){
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $return_data = array(true,$data[0]);
        return $return_data;
        }else{
        $return_data = array(false);
        return $return_data;
        }
    }

    public function update($channel){
        // get original data
        if(!isset($channel['id'])){
            // what the hell you are , bad guy!
            // don't give me shit
            return_fail('bad_request',"channel id does not include to update data");
        }
        $id = (int) isset($channel['id']) ? $channel['id'] : null;
        $orginal_data = $this->get($id);
        if(!$orginal_data[0]){
            return_fail('bad_request',"channel id does not exist in our database");
        }
        $org_channel = $orginal_data[1];
        $name = (string) isset($channel['name']) ?  $channel['name'] : $org_channel['name'];
        $poster = (string) isset($channel['poster']) ?  $channel['poster'] : $org_channel['poster'];
        $url = (string) isset($channel['url']) ?  $channel['url'] : $org_channel['url'];
        $country = (int) isset($channel['country']) ?  $channel['country'] : $org_channel['country'];
        $modified_date = date("Y-m-d"); // so even user data does not change, the modified date is changed and get success on update process

        $name = $this->xss_clean($name);
        // poster = url
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            
        } else {
            return_fail('CHANNEL update : invalid url',"invalid url");
        }
        $country = $this->xss_clean($country);
        $sql = "UPDATE `channel` SET `name` = ?, `poster` = ?, `url` = ?, `country` = ? WHERE `channel`.`id` = ?";
        $stmt = $this->conn->prepare($sql);
        if ( false === $stmt ) {
            return_fail('prepare_failed CHANNEL update',htmlspecialchars($this->conn->error));
        }
        $rc = $stmt->bind_param("sssii",$name,$poster,$url,$country,$id);
        if ( false===$rc ) {
            return_fail('bind_param_failed  CHANNEL update',htmlspecialchars($stmt->error));
        }
        $rc = $stmt->execute();
        if ( false===$rc ) {
            return_fail('execute_failed  CHANNEL update',htmlspecialchars($this->conn->errno).":".htmlspecialchars($stmt->error));
        }
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        if($affected_rows > 0 ){
            return_success("  CHANNEL update success",$affected_rows);
        }
        else{
            return_fail("  CHANNEL update fail : no data is updated",$affected_rows);
        }
    }

    public function delete($channel){
        // get original data
        if(!isset($channel['id'])){
            return_fail('bad_request',"channel id does not include to update data");
        }
        $id = (int) isset($channel['id']) ? $channel['id'] : null;
        $sql = "DELETE FROM channel WHERE id = ? ";
        $stmt = $this->conn->prepare($sql);
        if ( false === $stmt ) {
            return_fail('prepare_failed CHANNEL delete',htmlspecialchars($this->conn->error));
        }
        $rc = $stmt->bind_param("i",$id);
        if ( false===$rc ) {
            return_fail('bind_param_failed CHANNEL delete',htmlspecialchars($stmt->error));
        }
        $rc = $stmt->execute();
        if ( false===$rc ) {
            return_fail('execute_failed CHANNEL delete',htmlspecialchars($this->conn->errno).":".htmlspecialchars($stmt->error));
        }
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        if($affected_rows > 0 ){
            return_success(" CHANNEL delete success",$affected_rows);
        }
        else{
            return_fail(" CHANNEL delete : no data",$affected_rows);
        }
    }

} /// end for class




?>