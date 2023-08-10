<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image; 

class CategoryController extends Controller
{
    public function index(Request $request) {    //  listing
        $categories = Category::latest();
        if(!empty($request->get('keyword'))){
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');

        }

        $categories = $categories->paginate(10);

        return view('admin.category.list',compact('categories')); // sending categories into list of view
      
    }

    public function create() {  // create the category form
        
        return view('admin.category.create');
    }

    public function store(Request $request) {   // store the category form
       
        $validator = Validator::make($request->all(),[
        'name' => 'required',
        'slug' => 'required|unique:categories',
        'status' => 'required',
        ]);

        if($validator->passes()){

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome;
            $category->save();

            //save images here

            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id); // find the image
                $extArray = explode('.',$tempImage->name); // nature.jpg
                $ext = last($extArray); // get the last extension

                $newImageName = $category->id.'.'.$ext;
                $source_path = public_path().'/temp/'.$tempImage->name;
                $dest_path = public_path().'/uploads/category/'.$newImageName;
                File::copy($source_path,$dest_path);

                // generate Image Thumbnail
                $dest_path = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($source_path); //copy code from image intervention 
                $img = $img->fit(450, 600, function($constraint){
                    $constraint->upsize();
                });
                $img->save($dest_path);
                
                $category->img = $newImageName; // like 23.jpg
                $category->save();
            }

            $request->session()->flash('success','Category Added Successfully');

            return response([
                'status' => true,
                'message' => 'Category Added Successfully'
            ]);

        }else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        
    }


    public function edit($categoryId,Request $request) {  // edit the form
       
        $category = Category::find($categoryId);
        if(empty($category)){
            return redirect()->route('categories.index');
        } 
        return view('admin.category.edit',compact('category'));
    }

    public function update($categoryId, Request $request) { // update the category
            
        $category = Category::find($categoryId);

        if(empty($category)){

            $request->session()->flash('error', 'category not found');

                return response([
                    'status' => false,
                    'notFound' => true,
                    'messag' => 'Category Not Found'
                ]);
        } 
        // return view('admin.category.edit',compact('category'));
    

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
            'status' => 'required',   
        ]);

            
    
            if($validator->passes()){
    
                $category->name = $request->name;
                $category->slug = $request->slug;
                $category->status = $request->status;
                $category->showHome = $request->showHome;
                $category->save();
    
                //save images here
    
                if(!empty($request->image_id)){
                    $tempImage = TempImage::find($request->image_id); // find the image
                    $extArray = explode('.',$tempImage->name); // nature.jpg
                    $ext = last($extArray); // get the last extension
    
                    $newImageName = $category->id.'.'.$ext;
                    $source_path = public_path().'/temp/'.$tempImage->name;
                    $dest_path = public_path().'/uploads/category/'.$newImageName;
                    File::copy($source_path,$dest_path);
    
                    // generate Image Thumbnail
                    $dest_path = public_path().'/uploads/category/thumb/'.$newImageName;
                    $img = Image::make($source_path); //copy code from image intervention 
                    $img = $img->fit(450, 600, function($constraint){
                        $constraint->upsize();
                    });
                    $img->save($dest_path);
                    
                    $category->img = $newImageName; // like 23.jpg
                    $category->save();
                
                  
                }
    
                $request->session()->flash('success','Category Updated Successfully');
                return response([
                    'status' => true,
                    'message' => 'Category Updated Successfully'
                ]);
    
            }else{
                return response([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }
        }


    public function destroy($categoryId, Request $request) { // delete the single category
    $category = Category::find($categoryId);
    if (empty($category)){
        $request->session()->flash('error','Category not found');
        return redirect()->json([
            'status' => true,
            'message' => 'Category Not Found'
        ]);
    }

    File::delete(public_path().'/uploads/category/thumb/'.$category->img);
    File::delete(public_path().'/uploads/category/'.$category->img);

    $category->delete();
    
    $request->session()->flash('success','Category Deleted Successfully');
    
    return redirect()->json([
        'status' => true,
        'message' => 'Category Deleted Successfully'
    ]);
    
    }

}