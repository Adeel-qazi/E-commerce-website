<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Image;

class ProductImageController extends Controller
{

    public function update (Request $request) {

        $image = $request->image;
        // $extArray = explode('.',$image->name);
        // $ext = last($extArray);
        $ext = $image->getClientOriginalExtension(); 
        $sourcePath = $image->getPathName();   // temporary path of image 
        
        // save an image into database
        $productImage = new ProductImage();
        $productImage->product_id = $request->product->id;
        $productImage->image = 'NULL';
        $productImage->save();

        // give it a new name
        $imageName = $request->product->id.'-'.$productImage->id.'-'.time().'.'.$ext; // get unique name of image
        $productImage->name = $imageName;
        $productImage->save();


        // Generate Product Thumbnail
                    
        // larage image

        $destPath = public_path().'/uploads/product/large/'.$imageName;
        $image = Image::make($sourcePath);
        $image = resize(1400, null,function($constraint){
        $constraint->aspectRatio();
         });
        $image->save($destPath);


        // small image

        $destPath = public_path().'/uploads/product/small/'.$imageName;
        $image = Image::make($sourcePath);
        $image = fit(300,300);
        $image->save($destPath);



        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'imagePath' => asset('uploads/product/small/'.$productImage->image),
            'message' => 'Image saved successfully'
        ]);



    }



    public function destroy (Request $request) {

        $productImage = ProductImage::find($request->id);
    
        if(empty($productImage)){
            $request->session()->flash('error' ,'Image not found');
    
            return response()->json([
                'status' => false,
                'message' => 'Image not found'
            ]);
    
            // delete image from folder
            File::delete(public_path('uploads/product/small/'.$productImage->image));
            File::delete(public_path('uploads/product/large/'.$productImage->image));

            $productImage->delete();

            return response()->json([
                'status' => true,
                'message' => 'Image deleted successfully'
            ]);
    
        }
    
         }
                    
}
