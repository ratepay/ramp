<?php
/**
 * Created by PhpStorm.
 * User: annegretseufert
 * Date: 14.03.18
 * Time: 09:41
 */

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller;
use RatePAY;

class BaseController extends Controller
{

    /**
     * prepare head
     *
     * @param array $head
     * @return object
     */
    public function prepareHead($head) {
        $head = $this->_changeKeyFormat($head);
        $mbHead = new RatePAY\ModelBuilder();
        $mbHead->setArray($head);
        return $mbHead;
    }

    /**
     * prepare content
     *
     * @param array $content
     * @return object
     */
    public function prepareContent($content) {
        $content = $this->_changeKeyFormat($content);
        $mbContent = new RatePAY\ModelBuilder('Content');

        if (is_array($content['ShoppingBasket']['Items'])) {
            foreach ($content['ShoppingBasket']['Items'] AS $item) {
                $basketItems[] = array('Item' => $item);
            }
            $content['ShoppingBasket']['Items'] = $basketItems;
        }

        if (is_array($content['Customer']['Addresses'])) {
            foreach ($content['Customer']['Addresses'] AS $address) {
                $addresses[] = array('Address' => $address);
            }
            $content['Customer']['Addresses'] = $addresses;
        }

        $mbContent->setArray($content);
        return $mbContent;
    }

    /**
     * @param $content
     * @return array $content
     */
    protected function _changeKeyFormat($content) {
        foreach ($content AS $key => $value) {

            if (is_array($value)) {
                $value = $this->_changeKeyFormat($value);
            }
            unset($content[$key]);

            $key = ucwords($key, '_');
            $key = str_replace('_', '', $key);

            $content[ucfirst($key)] = $value;
        }
        return $content;
    }
}