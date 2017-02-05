<?php

namespace Api\Model;

use Base\Model\BaseModel;

class Category extends BaseModel
{
    public $id;
    public $mega_category_id;
    public $name;
    public $thumb_url;
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

    public function getMegaCategoryId()
    {
        return $this->mega_category_id;
    }

    public function setMegaCategoryId($mega_category_id)
    {
        $this->mega_category_id = $mega_category_id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getThumbUrl()
    {
        return $this->thumb_url;
    }

    public function setThumbUrl($thumb_url)
    {
        $this->thumb_url = $thumb_url;
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
