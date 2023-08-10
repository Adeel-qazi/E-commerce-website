<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{

    public function index(Request $request) {    //  listing of categories
       
        $subCategories = SubCategory::select('sub_categories.*','categories.name as categoryName')->latest('sub_categories.id')->leftJoin('categories','categories.id','sub_categories.category_id');

        if(!empty($request->get('keyword'))){
            $subCategories = $subCategories->where('sub_categories.name','like','%'.$request->get('keyword').'%');
            $subCategories = $subCategories->orWhere('categories.name','like','%'.$request->get('keyword').'%');
            
        }

        $subCategories = $subCategories->paginate(10);

        return view('admin.sub_category.list',compact('subCategories')); // sending categories into list of view
      
    }
    public function create(){
        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create',$data);
    }



    public function store (Request $request) {

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'status' => 'required',
            'category' => 'required',
        ]);

        if ($validator->passes()) {
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub Category Created Successfully');

            return response([
                'status' => true,
                'errors' => 'Sub Category Created Successfully'
            ]);


        }else{

            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);

        }

    }



    public function edit ($id, Request $request) {

        $subCategory = SubCategory::find($id);

        if (empty($subCategory)) {
            $request->session()->flash('error','Record not Found');
            return redirect()->route('sub-categories.index');

        }

        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;
        // dd($data['subCategories']);

        return view('admin.sub_category.edit',$data);

    }


    public function update ($id, Request $request) {
        
        $subCategory = SubCategory::find($id);

        if (empty($subCategory)) {
            $request->session()->flash('error','Record not Found');
            
            return response([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            'status' => 'required',
            'category' => 'required',
        ]);

        if ($validator->passes()) {
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub Category Updated Successfully');

            return response([
                'status' => true,
                'error' => 'Sub Category Updated Successfully'
            ]);


        }else{

            return response([
                'status' => false,
                'error' => $validator->errors()
            ]);

        }



    }


    public function destroy ($id, Request $request) {

        $subCategory = SubCategory::find($id);

        if (empty($subCategory)) {
            $request->session()->flash('error','Record not Found');
            return response([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $subCategory->delete();

        $request->session()->flash('success','Sub Category Deleted Successfully');

            return response([
                'status' => true,
                'error' => 'Sub Category Deleted Successfully'
            ]);




    }
}
