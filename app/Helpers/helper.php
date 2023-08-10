<?php
use App\Models\Category;

function getCategories(){    // it return all categories
    return Category::orderBy('name','ASC')
                    ->with('sub_category')      // this function is in Category model
                    ->orderBy('id','DESC')
                    ->where('status',1)
                    ->where('showHome','Yes')
                    ->get();
}
?>