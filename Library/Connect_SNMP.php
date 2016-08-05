<?php
/**
 * \brief Class Connect_SNMP
 *
 * Class Connect_SNMP  обеспечивает взаимодействие со свичами по snmp протоколу. создает сесию соединения со свичем по snmp протоколу
 *
 */

class Connect_SNMP
{
    private $snmp_session;
    private $version = SNMP::VERSION_2c;
    private $community_read;
    private $community_write;

    /**
     * \param $switch_ip ip свича
     * \param $wr_rd параметр определяющий режим взаиможействия со свичем - получение данных ('read'- значение 'r' - установленно
     * по умолчанию и 'read_write' - значение 'w')
     *
     * \return private $snmp_session установленную snmp сессию в виде свойства класса
     *
     * Получает из базы данных билинга информацию о настройках snmp в свиче $community_billing, если  snmp включено $community_billing['use_snmp']
     * или приложение работает в режиме - 'test', присваивает свойствам класса названия комьюнити на чтение и на запись. Комьюнити на
     * чтение из баы данных биллинга $community_billing['snmp_auth'] или если его нет комьюнити по умолчанию
     *\code
     * Config::get('community_read_default')
     *\endcode
     * комьюнити на запись устанавливается из конфигурационных настроек
     * *\code
     * Config::get('community_write_default')
     *\endcode
     * \todo Сделать получение комьюнити на запись из базы данных, аналогично комьюнити на чтение
     *
     * В зависимости от флага $wr_rd переменной $community присваевается или комьюнити на чтение или на запись.
     * Дале для установленной сессии добавляются необходимые настройки
     * * *\code
     * $this->snmp_session->exceptions_enabled = SNMP::ERRNO_ANY;
     * $this->snmp_session->valueretrieval = SNMP_VALUE_PLAIN;
     * $this->snmp_session->oid_increasing_check = false;
     *\endcode
     *
     * в случает не удачного подключения выбрасывается исключение
     */
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

    /// Закрывает snmp сессию
    public function close_session()
    {
        $this->snmp_session->close();
    }

    /**
     * \brief Получение данных по oid-у аналогично команде snmp get
     * \param $key ключь в виде единичного OID или массива с OID
     * \return $data  массив полученных данных или string значение если $key единичный OID
     */
    public function getByKey($key)
    {

        $data = $this->snmp_session->get($key, $preserve_keys = true);
        $this->close_session();

        return $data;
    }
    /**
     *
     * \brief Запись данных по oid-у аналогично команде snmp set
     * \param $object_id  OID на запись
     * \param $type тип записываемых данных (integer - 'i', string - 's')
     * \param $value записываемое значение
     *
     * В данном прилоджении эта функция используется только для проведения кабель теста. Используемый для этой цели oid
     * поизводит записть тестового значения и чтение полученных данных
     */
    public function setData($object_id,$type,$value)
    {

        $this->snmp_session->set($object_id,$type,$value);
        $this->close_session();

    }
    /**
     *
     * \brief Получение данных по oid-у аналогично команде snmp walk
     * \param $key - oid
     *
     * Используется для получение данных с испрользованием различных oid.
     * \return $data  массив полученных данных или string значение если $key единичный OID
     */
    public function walkByKey($key)
    {
       $data = $this->snmp_session->walk($key);
        $this->close_session();

        return $data;
    }

    /**
     * \brief Получение snmp сессии
     * \return SNMP сессию
     *
     * Можно использовать для обращения на прямую к методам и свойствам стандартного класса SNMP
     */
    public function getSnmpSession()
    {
        return $this->snmp_session;
    }





}

