<?php
/**
 * Created by PhpStorm.
 * User: annegretseufert
 * Date: 19.03.18
 * Time: 15:29
 */

class InstallmentTest extends TestCase
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
     * positive installment configuration test
     */
    public function testPositiveConfiguration()
    {
        $this->get('/installment', $this->_positive_header)
            ->seeJson(["successful" => true, "reason_code" => 306, "reason_message" => "Calculation configuration read successful"]);
    }

    /**
     * negative configuration test
     */
    public function testNegativeConfiguration()
    {
        $header = $this->_positive_header;
        $header['profile-id'] = 123;
        $this->get('/installment', $header)
             ->seeJson(["successful" => false,"reason_code" => 120, "reason_message" => "Authentication failed"]);
    }

    /**
     * positive installment configuration test
     */
    public function testPositiveTimeCalculation()
    {
        $this->json('POST','/installment', $this->_getTimeCalculation(), $this->_positive_header)
             ->seeJson(["successful" => true]);
    }

    /**
     * negative configuration test
     */
    public function testNegativeTimeCalculation()
    {
        $header = $this->_positive_header;
        $header['profile-id'] = 123;
        $this->json('POST','/installment', $this->_getTimeCalculation(), $header)
             ->seeJson(["successful" => false]);
    }

    /**
     * positive installment configuration test
     */
    public function testPositiveRateCalculation()
    {
        $this->json('POST','/installment', $this->_getRateCalculation(), $this->_positive_header)
            ->seeJson(["successful" => true]);
    }

    /**
     * negative configuration test
     */
    public function testNegativeRateCalculation()
    {
        $header = $this->_positive_header;
        $header['profile-id'] = 123;
        $this->json('POST','/installment', $this->_getRateCalculation(), $header)
            ->seeJson(["successful" => false]);
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
                            \"calculation_time\":{
                                \"month\":6
                            }
                        }
                    }
                }";
        return json_decode($json, true);
    }

    protected function _getRateCalculation()
    {
        $json = "{
                    \"options\":{
                        \"operation\":\"calculation-by-rate\"
                    },
                    \"content\":{
                        \"installment_calculation\":{
                            \"amount\":464.95,
                            \"calculation_rate\":{
                                \"rate\":50
                            }
                        }
                    }
                }";
        return json_decode($json, true);
    }
}