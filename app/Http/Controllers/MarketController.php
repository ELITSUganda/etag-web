<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketController extends Controller
{
    public function index(Request $r)
    {
        $isPjax = false;
        if ($r->headers->get('X-PJAX') != null) {
            $isPjax = true;
        }
        $products = Product::all();
        return  view('market.index', [
            'isPjax' => $isPjax,
            'products' => $products,
        ]);
    }


    public function product(Request $r, $slug)
    {

        $animal = Product::where([
            'slug' => $slug
        ])->first();

        $isPjax = false;
        if ($r->headers->get('X-PJAX') != null) {
            $isPjax = true;
        }

        if ($animal != null) {
            return  view('market.product', [
                'isPjax' => $isPjax,
                'product' => $animal,
            ]);
        }
        die("Page not found.");
    }
}
