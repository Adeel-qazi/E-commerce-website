<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;


class ProductController extends Controller
{

      public function index (Request $request){
                                        
       $products =  Product::latest('id')->with('product_images');     // relation in product model
       
       if(!empty($request->get('keyword'))){
         $products = $products->where('title','like','%'.$request->get('keyword').'%'); 
       }   
       $products = $products->paginate(10);         
       $data['products'] = $products;
    //    dd($products);

       return view("admin.products.list", $data);
          
        

      }




     public function create (){

        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        return view('admin.products.create',$data);

     }



     public function store (Request $request) {

        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];

        if(!empty($request->track_qty) && $request->track_qty == 'Yes'){
            $rules['qty'] = 'required';

        }

        $validator = Validator::make($request->all(),$rules);

        if($validator->passes()){

            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->short_description = $request->short_description;
            $product->description = $request->description;
            $product->shipping_returns = $request->shipping_returns;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode ;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();


            // saved Gallery Pics
            if(!empty($request->image_array)){
                foreach($request->image_array as $temp_image_id){
                       
                $tempImageInfo = TempImage::find($temp_image_id); // getting info form temporry image
                $extArray = explode('.',$tempImageInfo->name);
                $ext = last($extArray); // like jpg

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();
                    
                    $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext; // get unique name of image
                    $productImage->name = $imageName;
                    $productImage->save();


                    // Generate Product Thumbnail
                    
                    // larage image

                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destPath = public_path().'/uploads/product/large/'.$imageName;
                     $image = Image::make($sourcePath);
                     $image= $image->resize(1400, null,function($constraint){
                        $constraint->aspectRatio();
                     });
                     $image->save($destPath);


                     // small image

                    $destPath = public_path().'/uploads/product/small/'.$imageName;
                     $image = Image::make($sourcePath);
                     $image = $image->fit(300,300);
                     $image->save($destPath);

                }

            }

            $request->session()->flash('success','Product Added Successfully');

            return response()->json([
                'status' => true,
                'message' => 'Product Added Successfully'
                ]);

        }else{
            return response()->json([
            'status' => false,
            'errors' => $validator->errors()
            ]);
        }

     }

     public function edit ($id, Request $request) {
        $product = Product::find($id);
         
        $subCategories = SubCategory::where('category_id',$product->category_id)->get();// getting subCategory from with the help of categoryId
         
        // fetch Product Images
        $productImages = ProductImage::where('product_id',$product->id)->get();


        // Fetch Related Products
        $relatedProducts = [];
        if($product->related_products != ''){
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->get();

        }
         
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['product'] = $product;
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['subCategories'] = $subCategories;
        $data['productImages'] = $productImages;
        $data['relatedProducts'] = $relatedProducts;
        return view('admin.products.edit',$data);

     }



     public function update ($id, Request $request) {
        $product = Product::find($id);

        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$product->id.',id',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,'.$product->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];

        if(!empty($request->track_qty) && $request->track_qty == 'Yes'){
            $rules['qty'] = 'required';

        }

        $validator = Validator::make($request->all(),$rules);

        if($validator->passes()){

            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->short_description = $request->short_description;
            $product->description = $request->description;
            $product->shipping_returns = $request->shipping_returns;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode ;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();


           
            $request->session()->flash('success','Product Updated Successfully');

            return response()->json([
                'status' => true,
                'message' => 'Product Updated Successfully'
                ]);

        }else{
            return response()->json([
            'status' => false,
            'errors' => $validator->errors()
            ]);
        }


     }


     public function destroy ($id, Request $request){
      
        $product = Product::find($id);

        if(empty($product)){
            $request->session()->flash('error', 'Record not found');

            return response()->json([
                'status' => false,
                'notFound' => true
            ]);

        }

         $productImages = ProductImage::where('product_id',$id)->get();

         if(empty($productImages)){
            foreach($productImages as $productImage){
            File::delete(public_path('uploads/product/small/'.$productImage->name));
            File::delete(public_path('uploads/product/large/'.$productImage->name));

            }

            ProductImage::where('product_id',$id)->delete();


         }
        

        $product->delete();
         
        $request->session()->flash('success', 'Product deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);


     }


     public function getProducts(Request $request){

        $tempProduct = [];
        if($request->term != ""){
            $products = Product::where('title','like','%'.$request->term.'%')->get();

            if($products != null){
                foreach ( $products as $product ) {
                  $tempProduct[] = array('id' => $product->id, 'text' => $product->title);

                }

            }
        }

        return response()->json([
           'tags' => $tempProduct,
           'status' => true
        ]);

     }



   
}
