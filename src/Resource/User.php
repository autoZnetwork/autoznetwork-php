<?php

namespace AutozNetwork\Resource;

use AutozNetwork\ResourceBase;
use AutozNetwork\Resource\Product;

class User extends ResourceBase
{
  public function product()
  {
      return new Product($this);
  }

  // function addProduct($id, $productId) {
  //   return $this->sendRequest(
  //       'POST',
  //       $this->resourceName().'/'.strval($id).'/product',
  //       array(),
  //       array(
  //         'product' => $productId
  //       )
  //   );
  // }
}