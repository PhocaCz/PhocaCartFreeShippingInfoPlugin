<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.filesystem.file');
jimport( 'joomla.html.parameter' );


JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

class plgPCVFree_Shipping_Info extends JPlugin
{
	function __construct(& $subject, $config) {
		parent :: __construct($subject, $config);
		$this->loadLanguage();

	}


    public function renderProductInfo($product) {

        $display_product_info = $this->params->get('display_product_info', 0);
        $price = new PhocacartPrice();

        $o = array();


        if ($display_product_info == 1 && !empty($product) && isset($product['current_added']) && $product['current_added'] == 1)  {


            $path       = PhocacartPath::getPath('productimage');
            $image 	    = PhocacartImage::getThumbnailName($path, $product['image'], 'small');
            $altValue   = PhocaCartImage::getAltTitle($product['title'], $product['image']);



            $o[] = '<div class="row ph-plg-product-info">';

            $o[] = '<div class="row-item col-sm-6 col-md-6">';
            $o[] = '<img class="img-responsive ph-image" src="'.JURI::base(true).'/'.$image->rel.'"  alt="'.$altValue.'"  />';
            $o[] = '</div>';

            $o[] = '<div class="row-item col-sm-6 col-md-6">';
            $o[] = '<div class="ph-plg-product-info-title">'.$product['title'].'</div>';

            $o[] = '<ul class="ph-plg-product-attribute-box">';

            if (!empty($product['attributes'])) {
                foreach ($product['attributes'] as $k2 => $v2) {

                    if (!empty($v2)) {
                        foreach ($v2 as $k3 => $v3) {
                            $o[] = '<li class="ph-plg-product-attribute-item"><span class="ph-small ph-cart-small-attribute">' . $v3['atitle'] . ' ' . $v3['otitle'] . '</span>';

                            if (isset($v3['ovalue']) && urldecode($v3['ovalue']) != '') {
                                echo ': <span class="ph-small ph-cart-small-attribute">' . htmlspecialchars(urldecode($v3['ovalue']), ENT_QUOTES, 'UTF-8') . '</span>';
                            }
                            $o[] = '</li>';
                        }
                    }

                }
            }

            $o[] = '</ul>';

            $o[] = '<div class="ph-plg-product-quantity">'.JText::_('COM_PHOCACART_QUANTITY').': '.$product['current_quantity'].'</div>';
            $o[] = '<div class="ph-plg-product-price">'.JText::_('COM_PHOCACART_PRICE').': '.$price->getPriceFormat($product['brutto']).'</div>';
            $o[] = '</div>';
            $o[] = '</div>';


        }

        return trim(implode("\n", $o));
    }

    public function renderFreeShippingInfo($total) {

        $free_shipping_amount = $this->params->get('free_shipping_amount', '');

        $price = new PhocacartPrice();

        $o = array();

        if (isset($total[0]['brutto']) && $total[0]['brutto'] > 0 && $free_shipping_amount > 0) {



            if ($free_shipping_amount > $total[0]['brutto']) {

                $amountToDeliver = $free_shipping_amount - $total[0]['brutto'];
                $amountToDeliver = $price->getPriceFormat($amountToDeliver);
                $percentage      = $total[0]['brutto'] * 100 / $free_shipping_amount;

                $o[] = '<div class="ph-plg-free-shipping-info">'.JText::sprintf('PLG_PCV_FREE_SHIPPING_INFO_YOU_ARE_ONLY_AWAY_FROM_FREE_SHIPPING', $amountToDeliver).'</div>';


                $o[] = '<div class="ph-plg-free-shipping-info-progress progress">';
                $o[] = '<div class="progress-bar" role="progressbar" aria-valuenow="'.(int)$total[0]['brutto'].'" aria-valuemin="0" aria-valuemax="'.(int)$free_shipping_amount.'" style="width:'.(int)$percentage.'%" >';
                $o[] = '<span class="sr-only">70% Complete</span>';
                $o[] = '</div>';
                $o[] = '</div>';

                $o[] = '<div class="ph-plg-free-shipping-info-amounts">'. $price->getPriceFormat($total[0]['brutto']) .' / ' .$price->getPriceFormat($free_shipping_amount). '</div>';


            } else if ($free_shipping_amount < $total[0]['brutto'] || ($free_shipping_amount * 100) == ($total[0]['brutto'] * 100)) {
                /*
                $o[] = '<div class="ph-plg-free-shipping-info">'.  possible info about free shipping .'</div>';
                */
            }
        }
        return trim(implode("\n", $o));
    }


    /**
     * @param $context
     * @param $product All information about currenctly added product
     * @param $products All products in the cart
     * @param $total Cart total info
     * @return string
     */

	public function PCVonPopupAddToCartAfterHeader($context, $product, $products, $total) {

	    $o = $this->renderProductInfo($product);
        $o .= $this->renderFreeShippingInfo($total);
        return $o;

    }

    public function PCVonCheckoutAfterCart($context, $access, &$params, $total) {

        $display_checkout_view = $this->params->get('display_checkout_view', 0);
        if ($display_checkout_view == 0) {
            return false;
        }
        return $this->renderFreeShippingInfo($total);

    }
}
?>
