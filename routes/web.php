<?php

use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\Brandcontroller;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// frontend
Route::get('/',[FrontController::class,'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}',[ShopController::class,'index'])->name('front.shop');
Route::get('/product/{slug}',[ShopController::class,'product'])->name('front.product');
Route::get('/cart',[CartController::class,'cart'])->name('front.cart');
Route::post('/add-to-cart',[CartController::class,'addToCart'])->name('front.addToCart');
Route::post('/update-cart',[CartController::class,'updateCart'])->name('front.updateCart');
Route::post('/delete-item',[CartController::class,'deleteItem'])->name('front.deleteItem.cart');
Route::get('/checkout',[CartController::class,'checkout'])->name('front.checkout');
Route::post('/process-checkout',[CartController::class,'processCheckout'])->name('front.processCheckout');
Route::get('/thanks/{orderId}',[CartController::class,'thankyou'])->name('front.thankyou');
Route::post('/get-order-summary',[CartController::class,'getOrderSummary'])->name('front.getOrderSummary');



// Account
Route::group(['prefix'=>'account'],function(){
    Route::group(['middleware'=>'guest'],function(){  // apply the middleware for userAuthenticate

        Route::post('/process-register',[AuthController::class,'processRegister'])->name('account.processRegister');
        
        Route::get('/login',[AuthController::class,'login'])->name('account.login');
        Route::post('/login-authenticate',[AuthController::class,'authenticate'])->name('account.authenticate');
        Route::get('/register',[AuthController::class,'register'])->name('account.register');



});

Route::group(['middleware'=>'auth'],function(){  // apply the middleware for userAuthenticate
    Route::get('/profile',[AuthController::class,'profile'])->name('account.profile');   // if i directly access auth's route without login so it(authenticate middleware) redirects me into login page  
    Route::get('/logout',[AuthController::class,'logout'])->name('account.logout');      // if i directly access auth's route (login) used to be profile page so it(authenticate middleware) redirects me into same page (profile page) 


    });

});



Route::group(['prefix'=>'admin'],function(){

    Route::group(['middleware'=>'admin.auth'],function(){  // apply the middleware for adminAuthenticate
       
        Route::get('/dashboard', [HomeController::class,'index'])->name('admin.dashboard');  
        Route::get('/logout', [HomeController::class,'logout'])->name('admin.logout');
       
       
       
        //category routes
        Route::get('/categories', [CategoryController::class,'index'])->name('categories.index');  
        Route::get('/categories/create', [CategoryController::class,'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class,'store'])->name('categories.store');  
        Route::get('/categories/{category}/edit', [CategoryController::class,'edit'])->name('categories.edit');  
        Route::put('/categories/{category}', [CategoryController::class,'update'])->name('categories.update');  
        Route::delete('/categories/{category}', [CategoryController::class,'destroy'])->name('categories.delete');  
        
        // temp-images-create
        Route::post('/upload-temp-image', [TempImagesController::class,'create'])->name('temp-images.create');


        // Sub Category Routes
        Route::get('/sub-categories', [SubCategoryController::class,'index'])->name('sub-categories.index');  
        Route::get('/sub-categories/create', [SubCategoryController::class,'create'])->name('sub-categories.create');
        Route::post('/sub-categories', [SubCategoryController::class,'store'])->name('sub-categories.store');  
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class,'edit'])->name('sub-categories.edit');  
        Route::put('/sub-categories/{subCategory}', [SubCategoryController::class,'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class,'destroy'])->name('sub-categories.delete');  
        
   
        // Brands Routes
        Route::get('/brands', [Brandcontroller::class,'index'])->name('brands.index');  
        Route::get('brands/create', [Brandcontroller::class,'create'])->name('brands.create');
        Route::post('/brands', [Brandcontroller::class,'store'])->name('brands.store');  
        Route::get('/brands/{brand}/edit', [Brandcontroller::class,'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [Brandcontroller::class,'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [Brandcontroller::class,'destroy'])->name('brands.delete');  

    

        // Products Routes
        Route::get('/products', [ProductController::class,'index'])->name('products.index');  
        Route::get('products/create', [ProductController::class,'create'])->name('products.create');
        Route::post('/products', [ProductController::class,'store'])->name('products.store');  
        Route::get('/products/{product}/edit', [ProductController::class,'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class,'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class,'destroy'])->name('products.delete'); 
        Route::get('/get-products', [ProductController::class,'getProducts'])->name('products.getProducts');  

        // shipping
        Route::get('/shipping/create', [ShippingController::class,'create'])->name('shipping.create');  
        Route::post('/shipping', [ShippingController::class,'store'])->name('shipping.store');
        Route::get('/shipping/{shipp}', [ShippingController::class,'edit'])->name('shipping.edit');  
        Route::put('/shipping/{shipp}', [ShippingController::class,'update'])->name('shipping.update');  
        Route::delete('/shipping/{shipp}', [ShippingController::class,'destroy'])->name('shipping.delete');  
        

        
        


        Route::get('/product-subcategories', [ProductSubCategoryController::class,'index'])->name('product-subcategories.index');  

        Route::post('/product-images/update', [ProductImageController::class,'update'])->name('product-images.update'); 
        Route::delete('/product-images', [ProductImageController::class,'destroy'])->name('product-images.destroy');  

       
        
        

        
        

          
       
        

        Route::post('/getSlug', function (Request $request) {
        $slug = '';
        if (!empty($request->title)) {
        $slug = Str::slug($request->title);
        }
        return response()->json([
        'status' => true,
        'slug' => $slug,
         ]);
        })->name('getSlug');



           
    });

    Route::group(['middleware'=>'admin.guest'],function(){ // apply the middleware for adminRedirect
        
            Route::get('/login', [AdminLoginController::class,'index'])->name('admin.login');
            Route::post('/authenticate', [AdminLoginController::class,'authenticate'])->name('admin.authenticate');
            
    });

});