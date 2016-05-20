<?php


class Connect_SNMP
{
    private $snmp_session;
    private $version = SNMP::VERSION_2c;
    private $community_read;
    private $community_write;


    public function __construct($switch_ip, $wr_rd = 'r')
    {
        $indexModel = new IndexModel();
        $community_billing = $indexModel->getCommunity();
        if($community_billing['use_snmp'] || Config::get('mode') == 'test'){
            $this->community_read = $community_billing['snmp_auth'] ? $community_billing['snmp_auth'] : Config::get('community_read_default');
            $this->community_write = Config::get('community_write_default');
            if($wr_rd == 'w'){
                $community = $this->community_write;
            }elseif($wr_rd == 'r'){
                $community = $this->community_read;
            }else{
                throw new Exception('Wrong community flag', 500);
            }
            $this->snmp_session = new SNMP($this->version, $switch_ip, $community);
            $this->snmp_session->exceptions_enabled = SNMP::ERRNO_ANY;
            $this->snmp_session->valueretrieval = SNMP_VALUE_PLAIN;
            $this->snmp_session->oid_increasing_check = false;
        }else{
            throw new Exception('SNMP off in this switch', 1);
        }

    }

    public function close_session()
    {
        $this->snmp_session->close();
    }

    public function getByKey($key)
    {
        $data = $this->snmp_session->get($key, $preserve_keys = true);
        $this->close_session();

        return $data;
    }

    public function setData($object_id,$type,$value)
    {
        $this->snmp_session->set($object_id,$type,$value);
        $this->close_session();

    }

    public function walkByKey($key)
    {
       $data = $this->snmp_session->walk($key);
        $this->close_session();

        return $data;
    }

    /**
     * @return SNMP
     */
    public function getSnmpSession()
    {
        return $this->snmp_session;
    }





}

