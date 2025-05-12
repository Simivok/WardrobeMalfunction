<?php

namespace App\Http\Controllers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Contact;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\Slide;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;


class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at','DESC')->get()->take(10);
        $dashboardDatas =DB::select("SELECT 
                    SUM(total) AS TotalAmount,
                    SUM(IF(status='ordered', total, 0)) AS TotalOrderedAmount,
                    SUM(IF(status='delivered', total, 0)) AS TotalDeliveredAmount,
                    SUM(IF(status='canceled', total, 0)) AS TotalCanceledAmount,
                    COUNT(*) AS Total,
                    SUM(IF(status='ordered', 1, 0)) AS TotalOrdered,
                    SUM(IF(status='delivered', 1, 0)) AS TotalDelivered,
                    SUM(IF(status='canceled', 1, 0)) AS TotalCanceled
                FROM Orders;
        ");

        $montlyDatas = DB::select("SELECT M.id AS MonthNo, M.name AS MonthName,
                    IFNULL(D.TotalAmount,0) AS TotalAmount,
                    IFNULL(D.TotalOrderedAmount,0) AS TotalOrderedAmount,
                    IFNULL(D.TotalDeliveredAmount,0) AS TotalDeliveredAmount,
                    IFNULL(D.TotalCanceledAmount,0) AS TotalCanceledAmount FROM month_names M
                    LEFT JOIN (Select DATE_FORMAT(created_at, '%b')AS MontName,
                    MONTH(created_at) AS MonthNo,
         
                    SUM(IF(status !='canceled', total, 0)) AS TotalAmount,
                    SUM(IF(status='ordered', total, 0)) AS TotalOrderedAmount,
                    SUM(IF(status='delivered', total, 0)) AS TotalDeliveredAmount,
                    SUM(IF(status='canceled', total, 0)) AS TotalCanceledAmount
                    FROM Orders WHERE YEAR(created_at)=YEAR(NOW()) GROUP BY YEAR(created_at), MONTH(created_at) , DATE_FORMAT(created_at, '%b')
                    ORDER BY MONTH(created_at)) D ON D.MonthNo=M.id");

        $AmountM = implode(',',collect($montlyDatas)->pluck('TotalAmount')->toArray());
        $OrderedAmountM = implode(',',collect($montlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $DeliveredAmountM = implode(',',collect($montlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $CanceledAmountM = implode(',',collect($montlyDatas)->pluck('TotalCanceledAmount')->toArray());
        
        $TotalAmount = collect($montlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($montlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($montlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($montlyDatas)->sum('TotalCanceledAmount');

        return view('admin.index',compact('orders','dashboardDatas','AmountM','OrderedAmountM','DeliveredAmountM','CanceledAmountM','TotalAmount','TotalOrderedAmount','TotalDeliveredAmount','TotalCanceledAmount'));
    
    }
    public function brands(){

        $brands = Brand::orderBy('id','DESC')->paginate(10);
        return view('admin.brands',compact('brands'));
    }

    public function add_brand()  {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)  {
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:brands,slug',
            'image'=> 'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if ($request->file('image')) {
			$filePath = $request->file('image')->store('brands', 'public');
		}
        $brand->image = $filePath;
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been added successfully!'); 
    }

    public function brand_edit($id){
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }

    public function brand_update(Request $request){
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:brands,slug,'.$request->id,
            'image'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/brands').'/'.$brand->image)){
                File::delete(public_path('uploads/brands').'/'.$brand->image);
            }
            if ($request->file('image')) {
                $filePath = $request->file('image')->store('brands', 'public');
            }
            $brand->image = $filePath;
           
        };
        
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been updated successfully!');
    }

    
    public function brand_delete($id){
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands').'/'.$brand->image)) {
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status','Brand has been deleted successfully');
    }
    public function categories(){
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories',compact('categories'));
    }
    public function category_add(){
        return view('admin.category-add');
    }
    public function category_store(Request $request) 
    {
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:categories,slug',
            'image'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->file('image')) {
			$filePath = $request->file('image')->store('image', 'public');
		}
        $category->image = $filePath;
        $category->save();
        return redirect()->route('admin.categories')->with('status','Category has been added successfully!'); 
    }
    public function category_edit($id){
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }
    public function category_update(Request $request)
    {
        $request->validate([
            'name'=> 'required',
            'slug'=> 'required|unique:categories,slug,'.$request->id,
            'image'=> 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/categories').'/'.$category->image)){
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            if ($request->file('image')) {
                $filePath = $request->file('image')->store('image', 'public');
            }
            $category->image = $filePath;
           
        };
        
        $category->save();
        return redirect()->route('admin.categories')->with('status','Category has been updated successfully!');
    }
    public function category_delete($id){
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Category has been deleted successfully');
    }

    public function products()
    {
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }
    public function product_add()
    {
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories','brands'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
       
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $current_timestamp = Carbon::now()->timestamp;
        if ($request->file('image')) {
            $filePath = $request->file('image')->store('image', 'public');
            $product->image = $filePath;
        }
        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

       if ($request->hasFile('images')) 
       {
        $allowedfileExtion = ['jpg','png','jpeg'];
        $files = $request->file('images');
        foreach($files as $file){
            $gextension = $file->getClientOriginalExtension();
            $gcheck = in_array($gextension,$allowedfileExtion);
            if ($gcheck) 
            {
                $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                array_push($gallery_arr,$gfileName);
                $counter = $counter + 1;
                $filePath = $file->storeAs('images', $gfileName ,'public');
            }
        }
        $gallery_images = implode(',' ,$gallery_arr);
       }
       $product->images = $gallery_images;
        $product->save();
        return redirect()->route('admin.products')->with('status','Product has been added successfully');
    }
    
    public function product_edit($id)
{
    $product = Product::find($id);
    $categories = Category::select('id','name')->orderBy('name')->get();
    $brands = Brand::select('id','name')->orderBy('name')->get();
    return view('admin.product-edit',compact('product','categories','brands'));
}

public function product_update(Request $request)
{
    $request->validate([
        
        'name' => 'required',
        'slug' => 'required|unique:products,slug,'.$request->id,
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required',
        'sale_price' => 'required',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required',
        'brand_id' => 'required'
    ]);
        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $current_timestamp = Carbon::now()->timestamp;

        if ($request->file('image')) {
            if (File::exists(public_path('uploads/products').'/'.$product->image)) {
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            $filePath = $request->file('image')->store('image', 'public');
            $product->image = $filePath;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

       if ($request->hasFile('images')) 
       {
        foreach (explode(',',$product->images) as $ofile) {
            if (File::exists(public_path('uploads/products').'/'.$ofile)) {
                File::delete(public_path('uploads/products').'/'.$ofile);
            }
        }
        $allowedfileExtion = ['jpg','png','jpeg'];
        $files = $request->file('images');
        foreach($files as $file){
            $gextension = $file->getClientOriginalExtension();
            $gcheck = in_array($gextension,$allowedfileExtion);
            if ($gcheck) 
            {
                $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                array_push($gallery_arr,$gfileName);
                $counter = $counter + 1;
                $filePath = $file->storeAs('images', $gfileName ,'public');
            }
        }
        $gallery_images = implode(',' ,$gallery_arr);
        $product->images = $gallery_images;
       }
       
        $product->save();
        return redirect()->route('admin.products')->with('status','Product has been updated successfully!');
   } 
   public function product_delete($id){
    $product = Product::find($id);
    if (File::exists(public_path('uploads/products').'/'.$product->image)) {
        File::delete(public_path('uploads/products').'/'.$product->image);
    }
    foreach (explode(',',$product->images) as $ofile) {
        if (File::exists(public_path('uploads/products').'/'.$ofile)) {
            File::delete(public_path('uploads/products').'/'.$ofile);
        }
    
    $product->delete();
    return redirect()->route('admin.products')->with('status','Product has been deleted successfully!');
   }
    }
    public function coupons()
    {
    $coupons = Coupon::orderby('expiry_date','DESC')->paginate(12);
    return view('admin.coupons',compact('coupons'));
    }
    public function coupon_add()
    {
    return view('admin.coupon-add');
    }

    public function coupon_store(Request $request)
    {
        $request->validate([
        'code' => 'required',
        'type' => 'required',
        'value' => 'required|numeric',
        'cart_value' => 'required|numeric',
        'expiry_date' => 'required|date'

    ]);
    $coupon = new Coupon();
    $coupon->code = $request->code;
    $coupon->type = $request->type;
    $coupon->value = $request->value; 
    $coupon->cart_value = $request->cart_value;
    $coupon->expiry_date = $request->expiry_date;
    $coupon->save();
    return redirect()->route('admin.coupons')->with('status','Coupon has been added successfully!');
    }
    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit',compact('coupon'));
    }

    public function coupon_update(Request $request)
    {
        $request->validate([
        'code' => 'required',
        'type' => 'required',
        'value' => 'required|numeric',
        'cart_value' => 'required|numeric',
        'expiry_date' => 'required|date'

        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value; 
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status','Coupon has been updated successfully!');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status','Coupon has been deleted successfully!');
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admin.orders',compact('orders'));
    }

    public function order_details($order_id)
    {   
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first();
        return view('admin.order-details', compact('order','orderItems','transaction'));

    }
    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if ($request->order_status == 'delivered') 
        {
            $order->delivered_date = Carbon::now();
        }
        elseif ($request->order_status == 'canceled') 
        {
            $order->canceled_date = Carbon::now();
        }
        $order->save();

        if ($request->order_status == 'delivered') 
        {
            $transaction = Transaction::where('order_id',$request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with("status","Status changed successfully");
    }
    public function slides()
    {
        $slides = Slide::orderBy('id','DESC')->paginate(12);
        return view('admin.slides',compact('slides'));
    }
    public function slide_add()
    {

        return view('admin.slide-add');   
    }
    public function slide_store(Request $request)
    {
        $request->validate
        ([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'status' => 'required',
            'link' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'

        ]);
        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if ($request->file('image')) {
			$filePath = $request->file('image')->store('image', 'public');
		}
        $slide->image = $filePath;
        $slide->save();
        return redirect()->route('admin.slides')->with("status","Slide added successfully");
    }
    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit',compact('slide'));

    } 

    public function slide_update(Request $request)
    {
        $request->validate
        ([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'status' => 'required',
            'link' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048'

        ]);
        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/slides').'/'.$slide->image))
            {
                File::delete(public_path('uploads/slides').'/'.$slide->image);
            }
            if ($request->file('image')) {
                $filePath = $request->file('image')->store('image', 'public');
            }
        $slide->image = $filePath;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with("status","Slide has been updated successfully");
    }
    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if(File::exists(public_path('uploads/slides').'/'.$slide->image))
            {
                File::delete(public_path('uploads/slides').'/'.$slide->image);
            }
            $slide->delete();
            return redirect()->route('admin.slides')->with("status","Slide has been deleted successfully");
    }

    public function contacts()
    {
        $contacts = Contact::orderBy('created_at','DESC')->paginate(10);
        return view('admin.contacts',compact('contacts'));
    }
    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contacts')->with("status","Message deleted successfully");
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name','LIKE',"%{$query}%")->get()->take(8);
        return response()->json($results);
    }
    

    public function apiGetProducts(){
        $products = Product::all();
        return response()->json($products);
    }
}