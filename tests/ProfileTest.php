<?php
/**
 * Created by PhpStorm.
 * User: annegretseufert
 * Date: 16.03.18
 * Time: 09:16
 */

class ProfileTest extends TestCase
{
    var $_positive_header;
    var $_negative_header;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $profile = getenv('TEST_PROFILE');
        $security = getenv('TEST_SECURITY_CODE');

        $this->_positive_header = array('profile-id' => $profile,
            'security-code' => $security,
            'system-id' => 'test',
            'sandbox' => true);
        $this->_negative_header = $this->_positive_header;
        $this->_negative_header['profile-id'] = $this->_negative_header['profile-id'] . '1';
    }

    public function testPositiveProfile()
    {

        $this->get('/profile', $this->_positive_header)
             ->seeJson(["name" => "DEV_1.8_DE"]);
    }

    public function testNegativeProfile()
    {
        $this->get('/profile', $this->_negative_header)
            ->seeJson(["successful" => false, "reason_message" => "Authentication failed"]);
    }
}