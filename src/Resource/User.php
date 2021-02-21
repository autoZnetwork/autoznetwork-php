<?php

namespace AutozNetwork\Rest;

use AutozNetwork\ResourceBase;

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
