<?php
namespace Magepow\CheckoutUpsell\Block\Checkout;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context as ContextProduct;

class ProductUpsell extends Template{
 /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    protected $_cart;
    protected $imageBuilder;
    /**
     * @var LoggerInterface 
     */
    private $logger;
 
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ContextProduct $contextProduct,
        ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        LoggerInterface $logger,
        array $data = []
    ) { 

        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->_cart = $cart;
        $this->imageBuilder = $contextProduct->getImageBuilder();
        parent::__construct($context, $data);
    }
    public function getProductData()
        {
            $items = [];
            $productInfo = $this->_cart->getQuote()->getItemsCollection();
            foreach ($productInfo as $item){
                $items[] = $item->getProductId();
            }

            return $items;
        }
    /**
     * upsell product
     *
     * @return array
     */
    public function getUpsellProducts()
    {   
        $upsellProduct = array();
        $productIds = $this->getProductData();
        foreach ($productIds as $productId) {
            $product = $this->productRepository->getById($productId);
            $upsell = $product->getUpSellProducts();
            if (count($upsell)) {
                foreach ($upsell as $item) {
                    $upsellProduct[] = $item->getId();
                }
            }
        }
         return $upsellProduct;
    }
   /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }
    /**
     * getProductPrice ]
     * @param  \Magento\Catalog\Model\Product $product 
     * @return productPrice                                 
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return $this->getProductPriceHtml(
            $product,
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
        );
    }
    /**
     * [getProductPriceHtml description]
     * @param  \Magento\Catalog\Model\Product $product    
     * @param                                 $priceType  
     * @param                                 $renderZone 
     * @param  array                          $arguments  
     * @return $price                                   
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render($priceType, $product, $arguments);
        }
        return $price;
    }  
}
