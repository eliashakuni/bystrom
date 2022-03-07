<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once 'helper-functions.php';

/* 
* Make the price look correct
*
* The 'price dispmay suffix' in WooCommerce -> VAT Compliance -> Settings -> Other WooCommerce tax options potentially 
* relevant for VAT compliance should be 
* "{inc}{price_including_tax}{/inc}{exc}{price_excluding_tax}{/exc}{ctry}{iftax}{country_with_brackets}{/iftax}{/ctry}" 
* (without quotation marks) 
*/

add_filter('woocommerce_get_price_html', 'esh_price_format');
function esh_price_format($price) {
    preg_match('#{inc}(.*){/inc}#', $price, $matches);
    $inc_tax = $matches[1];
    preg_match('#{ctry}(.*){/ctry}#', $price, $matches);
    $country = $matches[1];
    preg_match('#{exc}(.*){/exc}#', $price, $matches);
    $exc_tax = $matches[1];
    $type_of_page = is_product() ? 'price-single-product' : 'price-product-category';

    if (esh_is_current_user_a_business()) {
        $price_html = '<span class="price-block-main ' . $type_of_page . '">' . $exc_tax . '<small class="woocommerce-price-suffix">' . esc_html__('exklusive moms', 'bystrom-functions') . '</small></span><span class="price-block-secondary ' . $type_of_page . '">' . $inc_tax . '<small class="woocommerce-price-suffix">' . esc_html__('inklusive moms', 'bystrom-functions') . ' ' . $country . '</small></span>';
    } else {
        $price_html = '<span class="price-block-main ' . $type_of_page . '">' . $inc_tax . '<small class="woocommerce-price-suffix">' . esc_html__('inklusive moms', 'bystrom-functions') . ' ' . $country . '</small></span>';
    }
    return $price_html;
}

function esh_is_current_user_a_business() {
    return esh_is_current_user(array("administrator", "wholesaler"));
}

/**
 * Fix the price format for variable products.
 */

add_filter('woocommerce_get_price_suffix', 'esh_variable_price_suffix', 10, 4);
function esh_variable_price_suffix($html, $product, $price, $qty) {
    if (!$html && $product instanceof WC_Product_Variable) {
        // Copied from plugins/woocommerce/includes/abstracts/abstract-wc-product.php#get_price_suffix
        if (($suffix = get_option('woocommerce_price_display_suffix'))
            && wc_tax_enabled()
            && 'taxable' === $product->get_tax_status()
        ) {
            $replacements = array(
                '{price_including_tax}' => wc_price(wc_get_price_including_tax($product, array('qty' => $qty, 'price' => $price))),
                '{price_excluding_tax}' => wc_price(wc_get_price_excluding_tax($product, array('qty' => $qty, 'price' => $price))),
            );
            $html = str_replace(array_keys($replacements), array_values($replacements), ' <small class="woocommerce-price-suffix">' . wp_kses_post($suffix) . '</small>');
        }
    }

    return $html;
};

/*print("Hello world!");

print('H'+'e'+'l'+'l'+'o'+' '+'w'+'o'+'r'+'l'+'d'+'!');

str hello = 'Hello world!';
for(int i = 0; i < hello.length(); i++) {
    print(hello[i]);
}

for(int i = (int)false; i < 'Hello world!'.length(), i++) {
    if(i==0) {
        print('H');
    }else if(i==1) {
        print('e');
    }else if(i==2 || i==3 || i==9) {
        print('l');
    }else if(i==4 || i==7) {
        print('o');
    }else if(i==5) {
        print(' ');
    }else if(i==6) {
        print('w');
    }else if(i==8) {
        print('r');
    }else if(i==10) {
        print('d');
    }else if(i==11) {
        print('!');
    }
}*/