<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index (Request $request, $categorySlug = null, $subCategorySlug = null) {

        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        
      



       
        $categories = Category::orderBy('name','ASC')->with('sub_category')->where('status',1)->get();
        $brands     = Brand::orderBy('name','ASC')->where('status',1)->get();

        $products   = Product::where('status',1); //1

         // apply filters here
        if(!empty($categorySlug)){
            $category = Category::where('slug',$categorySlug)->first();  // we get category data from helping of slug
            $products  = $products->where('category_id',$category->id);   // we get product data from helping of category_id
            $categorySelected = $category->id;


        }

        if(!empty($subCategorySlug)){
            $subCategory = SubCategory::where('slug',$subCategorySlug)->first();  // we get category data from helping of slug
            $products  = $products->where('sub_category_id',$subCategory->id);   // we get product data from helping of sub_category_id
            $subCategorySelected    = $subCategory->id;          

        }


         // Brand Filter
        if(!empty($request->get('brand'))){

            $brandsArray = explode(',', $request->get('brand')); // value is stored into array with comma seperated
            $products = $products->whereIn('brand_id',$brandsArray);   

         }

         // for price filter
        if($request->get('price_max') != '' && $request->get('price_min') != ''){   
            if($request->get('price_max') == 1000){
                $products = $products->whereBetween('price',[intval($request->get('price_min')),100000]);  //intval(): it transform string into integer   
            }else{
                                          // mostly this one runs
                $products = $products->whereBetween('price',[intval($request->get('price_min')),intval($request->get('price_max'))]);  //intval(): it transform string into integer   
            }
        }

         
        // Sort Filter
        if($request->get('sort') != ''){

            if($request->get('sort') == 'latest'){
             $products = $products->orderBy('id','DESC');  //2
            
            }else if($request->get('sort') == 'price_asc'){
                $products = $products->orderBy('price','ASC');
            
            }else{
                $products = $products->orderBy('price','DESC');
            }
        }else{
            $products = $products->orderBy('id','DESC');  //2
        }


        

        $products   = $products->paginate(6);   // 3

        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;
        $data['categorySelected']  = $categorySelected;
        $data['subCategorySelected']  = $subCategorySelected;
        $data['brandsArray']  = $brandsArray;
        $data['priceMax']  = (intval($request->get('price_max'))== 0) ? 1000 :intval($request->get('price_max')) ;  // mostly else part runs
        $data['priceMin']  = intval($request->get('price_min'));
        $data['sort']  = $request->get('sort');


        return view("front.shop",$data);
    }

    


    public function product($slug){   // single Product
        $product = Product::where('slug',$slug)->with('product_images')->first();   // along relation of product's image in product model 
      
        if($product == null){
            abort(404);
        }

         // Fetch Related Products
         $relatedProducts = [];
         if($product->related_products != ''){
             $productArray = explode(',',$product->related_products);
             $relatedProducts = Product::whereIn('id', $productArray)->with('product_images')->get();
 
         }

        $data['product'] = $product;
        $data['relatedProducts'] = $relatedProducts;
        return view('front.product',$data);     // goto Product page
    }
}
