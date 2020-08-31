<?php

namespace AutozNetwork;

interface ResourceInterface
{
    public function all(array $options = []);
    public function create(array $data, array $headers = null);
    public function get($id);
    public function delete($id);
}
