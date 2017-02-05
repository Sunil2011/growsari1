<?php

namespace Api\Model;

use Base\Model\BaseModel;

class Product extends BaseModel
{
    public $id;
    public $category_id;
    public $brand_id;
    public $sku_id;
    public $item_code;
    public $super8_name;
    public $barcode;
    public $volume;
    public $sku;
    public $variant_color;
    public $image;
    public $display;
    public $format;
    public $quantity;
    public $promo;
    public $is_deleted;
    public $super8_price;
    public $price;
    public $srp;
    public $status;
    public $is_promotional;
    public $is_recommended;
    public $is_new;
    public $is_locked;
    public $is_available;
    public $created_at;
    public $updated_at;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCategoryId()
    {
        return $this->category_id;
    }

    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
    }

    public function getBrandId()
    {
        return $this->brand_id;
    }

    public function setBrandId($brand_id)
    {
        $this->brand_id = $brand_id;
    }

    public function getSkuId()
    {
        return $this->sku_id;
    }

    public function setSkuId($sku_id)
    {
        $this->sku_id = $sku_id;
    }

    public function getItemCode()
    {
        return $this->item_code;
    }

    public function setItemCode($item_code)
    {
        $this->item_code = $item_code;
    }

    public function getSuper8Name()
    {
        return $this->super8_name;
    }

    public function setSuper8Name($super8_name)
    {
        $this->super8_name = $super8_name;
    }

    public function getBarcode()
    {
        return $this->barcode;
    }

    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

    public function getVolume()
    {
        return $this->volume;
    }

    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    public function getVariantColor()
    {
        return $this->variant_color;
    }

    public function setVariantColor($variant_color)
    {
        $this->variant_color = $variant_color;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function setDisplay($display)
    {
        $this->display = $display;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getPromo()
    {
        return $this->promo;
    }

    public function setPromo($promo)
    {
        $this->promo = $promo;
    }

    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    public function setIsDeleted($is_deleted)
    {
        $this->is_deleted = $is_deleted;
    }

    public function getSuper8Price()
    {
        return $this->super8_price;
    }

    public function setSuper8Price($super8_price)
    {
        $this->super8_price = $super8_price;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getSrp()
    {
        return $this->srp;
    }

    public function setSrp($srp)
    {
        $this->srp = $srp;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getIsPromotional()
    {
        return $this->is_promotional;
    }

    public function setIsPromotional($is_promotional)
    {
        $this->is_promotional = $is_promotional;
    }

    public function getIsRecommended()
    {
        return $this->is_recommended;
    }

    public function setIsRecommended($is_recommended)
    {
        $this->is_recommended = $is_recommended;
    }

    public function getIsNew()
    {
        return $this->is_new;
    }

    public function setIsNew($is_new)
    {
        $this->is_new = $is_new;
    }

    public function getIsLocked()
    {
        return $this->is_locked;
    }

    public function setIsLocked($is_locked)
    {
        $this->is_locked = $is_locked;
    }

    public function getIsAvailable()
    {
        return $this->is_available;
    }

    public function setIsAvailable($is_available)
    {
        $this->is_available = $is_available;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }
}
