<?php
/**
 * Created by PhpStorm.
 * User: annegretseufert
 * Date: 20.03.18
 * Time: 14:15
 */


class ExceptionSDKTest extends TestCase
{
    var $_positive_header;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $profile = getenv('TEST_PROFILE');
        $security = getenv('TEST_SECURITY_CODE');

        $this->_positive_header = array('profile-id' => $profile,
            'security-code' => $security,
            'system-id' => 'test',
            'sandbox' => true);
    }

    /**
     * negative configuration test
     */
    public function testFormatException()
    {
        $header = $this->_positive_header;
        $this->json('POST','/installment', $this->_getTimeCalculation(), $header);
        $exception = $this->response->exception;
        $this->assertTrue(!empty($exception));
    }

    protected function _getTimeCalculation()
    {
        $json = "{
                    \"options\":{
                        \"operation\":\"calculation-by-time\"
                    },
                    \"content\":{
                        \"installment_calculation\":{
                            \"amount\":464.95,
                            \"calculation_time\":6
                        }
                    }
                }";
        return json_decode($json, true);
    }
}