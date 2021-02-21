<?php

namespace AutozNetwork\Rest;

interface ResourceInterface
{
    public function all(array $options = []);

    public function get($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);
}
