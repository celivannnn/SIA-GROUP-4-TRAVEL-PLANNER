<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TravelFormService
{
    protected $url;
    protected $key;

    public function __construct()
    {
        $this->url = config('services.travel_form.url');
        $this->key = config('services.travel_form.key');
    }

    public function getAll()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->get("{$this->url}/travel-form")->json();
    }

    public function getById($id)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->get("{$this->url}/travel-form/{$id}")->json();
    }

    public function create(array $data)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->post("{$this->url}/travel-form", $data)->json();
    }

    public function update($id, array $data)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->put("{$this->url}/travel-form/{$id}", $data)->json();
    }

    public function delete($id)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->delete("{$this->url}/travel-form/{$id}")->json();
    }
}
