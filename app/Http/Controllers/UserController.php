<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function index(){     
        $user = auth()->user();
        $ordersCount = Order::where('user_id', $user->id)->count();
         $productCount = Product::count();
        return view('user.index', compact('ordersCount','productCount'));
        
    }

    public function orders(){

        $orders = Order::where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->paginate(10);
        return view('user.orders',compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::where('user_id',Auth::user()->id)->where('id',$order_id)->first();
        if ($order) {
            $orderItems = OrderItem::where('order_id',$order->id)->orderBy('id')->paginate(12);
            $transaction = Transaction::where('order_id',$order->id)->first();
            return view('user.order-details',compact('order','orderItems','transaction'));
        }
        else {
            return redirect()->route('login');
        }
          
    }
    public function order_cancel(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = "canceled";
        $order->canceled_date = Carbon::now();
        $order->save();
        return back()->with('status',"Order has been canceled successfully");
    }
    public function editPassword()
    {
        return view('user.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'A jelenlegi jelszó hibás.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'A jelszó sikeresen frissítve.');
    }
    public function editEmail()
    {
        return view('user.change-email');
    }

        public function updateEmail(Request $request)
    {
        $request->validate([
            'current_email' => 'required|email',
            'new_email' => 'required|email|unique:users,email',
        ]);

        if ($request->current_email !== auth()->user()->email) {
            return back()->withErrors(['current_email' => 'A megadott jelenlegi email nem egyezik.']);
        }

        auth()->user()->update(['email' => $request->new_email]);

        return back()->with('status', 'Az email cím sikeresen frissítve.');
    }

    
}
