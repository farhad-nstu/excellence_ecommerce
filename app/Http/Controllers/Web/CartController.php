<?php
namespace App\Http\Controllers\Web;

use App\Models\Web\Cart;
use App\Models\Web\Index;
use App\Models\Web\Products;
use App\Models\Core\ProductAttribute;
use Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lang;
use Session;
use DB;

class CartController extends Controller
{

    public function __construct(
        Index $index,
        Products $products,
        Cart $cart
    ) {
        $this->index = $index;
        $this->products = $products;
        $this->cart = $cart;
        $this->theme = new ThemeController();

    }

    //myCart
    public function viewcart(Request $request)
    {
        $title = array('pageTitle' => Lang::get("website.View Cart"));
        $result = array();
        $data = array();
        $result['commonContent'] = $this->index->commonContent();
        $final_theme = $this->theme->theme();

        $result['cart'] = $this->cart->myCart($data);
        // dd($result['cart']);
        // $quantities = ProductAttribute::where('product_id', $result['cart'][0]->products_id)->where('choice_option', $result['cart'][0]->products_options_values)->get();
        
        //apply coupon
        if (session('coupon')) {
            $session_coupon_data = session('coupon');
            session(['coupon' => array()]);
            $response = array();
            if (!empty($session_coupon_data)) {
                foreach ($session_coupon_data as $key => $session_coupon) {
                    $response = $this->cart->common_apply_coupon($session_coupon->code);
                }
            }
        }
        return view("web.carts.viewcart", ['title' => $title, 'final_theme' => $final_theme])->with('result', $result);
    }

    //eidtCart
    public function editcart(Request $request, $id, $slug)
    {

        $title = array('pageTitle' => Lang::get('website.Product Detail'));
        $result = array();
        $result['commonContent'] = $this->index->commonContent();
        $final_theme = $this->theme->theme();
        //min_price
        if (!empty($request->min_price)) {
            $min_price = $request->min_price;
        } else {
            $min_price = '';
        }

        //max_price
        if (!empty($request->max_price)) {
            $max_price = $request->max_price;
        } else {
            $max_price = '';
        }

        if (!empty($request->limit)) {
            $limit = $request->limit;
        } else {
            $limit = 15;
        }

        $products = $this->products->getProductsBySlug($slug);

        //category
        $category = $this->products->getCategoryByParent($products[0]->products_id);

        if (!empty($category)) {
            $category_slug = $category[0]->categories_slug;
            $category_name = $category[0]->categories_name;
        } else {
            $category_slug = '';
            $category_name = '';
        }
        $sub_category = $this->products->getSubCategoryByParent($products[0]->products_id);

        if (!empty($sub_category) and count($sub_category) > 0) {
            $sub_category_name = $sub_category[0]->categories_name;
            $sub_category_slug = $sub_category[0]->categories_slug;
        } else {
            $sub_category_name = '';
            $sub_category_slug = '';
        }

        $result['category_name'] = $category_name;
        $result['category_slug'] = $category_slug;
        $result['sub_category_name'] = $sub_category_name;
        $result['sub_category_slug'] = $sub_category_slug;

        $isFlash = $this->products->getFlashSale($products[0]->products_id);

        if (!empty($isFlash) and count($isFlash) > 0) {
            $type = "flashsale";
        } else {
            $type = "";
        }

        $data = array('page_number' => '0', 'type' => $type, 'products_id' => $products[0]->products_id, 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
        $detail = $this->products->products($data);
        $result['detail'] = $detail;

        $i = 0;
        foreach ($result['detail']['product_data'][0]->categories as $postCategory) {
            if ($i == 0) {
                $postCategoryId = $postCategory->categories_id;
                $i++;
            }
        }

        $data = array('page_number' => '0', 'type' => '', 'categories_id' => $postCategoryId, 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
        $simliar_products = $this->products->products($data);
        $result['simliar_products'] = $simliar_products;

        $cart = '';
        $result['cartArray'] = $this->products->cartIdArray($cart);

        //liked products
        $result['liked_products'] = $this->products->likedProducts();

        $data = array('page_number' => '0', 'type' => 'topseller', 'limit' => $limit, 'min_price' => $min_price, 'max_price' => $max_price);
        $top_seller = $this->products->products($data);
        $result['top_seller'] = $top_seller;

        $result['cart'] = $this->cart->myCart($id);

        return view("web.detail", ['title' => $title, 'final_theme' => $final_theme])->with('result', $result);

    }

    //deleteCart
    public function deleteCart(Request $request)
    {
        $proId = DB::table('customers_basket')->where([
            ['customers_basket_id', '=', $request->id],
        ])->select('products_id')->get();
        $prodcutId = $proId[0]->products_id;

        DB::table('new_customers_basket_attributes')->where([
            ['products_id', '=', $prodcutId],
            ['is_orders', '=', '0'],
        ])->delete();


        $check = $this->cart->deleteCart($request);
        //apply coupon
        if (!empty(session('coupon')) and count(session('coupon')) > 0) {
            $session_coupon_data = session('coupon');
            session(['coupon' => array()]);
            if (count($session_coupon_data) == '2') {
                $response = array();
                if (!empty($session_coupon_data)) {
                    foreach ($session_coupon_data as $key => $session_coupon) {
                        $response = $this->cart->common_apply_coupon($session_coupon->code);
                    }
                }
            }
        }

        if (!empty($request->type) and $request->type == 'header cart') {
            $result['commonContent'] = $this->index->commonContent();
            if (empty($check)) {
                $message = Lang::get("website.Cart item has been deleted successfully");
                return redirect('/')->with('message', $message);

            } else {
                $message = Lang::get("website.Cart item has been deleted successfully");
                $final_theme = $this->index->finalTheme();
                return view("web.headers.cartButtons.cartButton".$final_theme->header)->with('result', $result);
            }
        } else {
            if (empty($check)) {
                $message = Lang::get("website.Cart item has been deleted successfully");
                return redirect('/')->with('message', $message);

            } else {
                $message = Lang::get("website.Cart item has been deleted successfully");
                return redirect()->back()->with('message', $message);
            }
        }
    }

    //getCart
    public function cartIdArray($request)
    {
        $this->cart->cartIdArray($request);
    }

    //updatesinglecart
    public function updatesinglecart(Request $request)
    {
        $this->cart->updatesinglecart($request);
        $final_theme = $this->index->finalTheme();
        return view("web.headers.cartButtons.cartButton".$final_theme->header)->with('result', $result);
    }

    //addToCart
    public function addToCart(Request $request)
    {
        $result = $this->cart->addToCart($request);
        // dd($result);
        if (!empty($result['status']) && $result['status'] == 'exceed') {
            return $result;
        }
        
        $final_theme = $this->index->finalTheme();
        return view("web.headers.cartButtons.cartButton".$final_theme->header)->with('result', $result);
    }

    //addToCartFixed
    public function addToCartFixed(Request $request)
    {
        $result['commonContent'] = $this->index->commonContent();        
        return view("web.headers.cartButtons.cartButtonFixed")->with('result', $result);
    }

    public function addToCartResponsive(Request $request)
    {
        $result['commonContent'] = $this->index->commonContent();        
        return view("web.headers.cartButtons.cartButton")->with('result', $result);
    } 

    //updateCart
    public function updateCart(Request $request)
    {
        // dd($request->all());

        if (empty(session('customers_id'))) {
            $customers_id = '';
        } else {
            $customers_id = session('customers_id');
        }
        $session_id = Session::getId();
        $this->cart->updateRecord($request->cart, $customers_id, $session_id, $request->quantity, $request->products_price);

        $message = Lang::get("website.Cart has been updated successfully");
        return redirect()->back()->with('message', $message);

    }

    //apply_coupon
    public function apply_coupon(Request $request)
    {

        $result = array();
        $coupon_code = $request->coupon_code;

        $carts = $this->cart->myCart(array());
        if (count($carts) > 0) {
            $response = $this->cart->common_apply_coupon($coupon_code);
        } else {
            $response = array('success' => '0', 'message' => Lang::get("website.Coupon can not be apllied to empty cart"));
        }
        print_r(json_encode($response));
    }

    //removeCoupon
    public function removeCoupon(Request $request)
    {
        $coupons_id = $request->id;

        $session_coupon_data = session('coupon');
        session(['coupon' => array()]);
        session(['coupon_discount' => 0]);
        $response = array();
        if (!empty($session_coupon_data)) {
            foreach ($session_coupon_data as $key => $session_coupon) {
                if ($session_coupon->coupans_id != $coupons_id) {
                    $response = $this->cart->common_apply_coupon($session_coupon->code);
                }
            }
        }

        $message = Lang::get("website.Coupon has been removed successfully");
        return redirect()->back()->with('message', $message);

    }


    public function increase_qty(Request $request)
    {
        $originalPrice = $request->price/$request->qty;

        DB::table('customers_basket')->where('customers_basket_id', '=', $request->busket_id)->update(
            [
                'customers_basket_quantity' => $request->qty + 1,
                'final_price' => $originalPrice*($request->qty + 1),
            ]);        

        $basket = DB::table('customers_basket')->where([
            ['customers_basket_id', '=', $request->busket_id],
        ])->select('customers_basket_quantity','final_price', 'products_id')->get();
        return response()->json($basket);
       
    }

    public function decrease_qty(Request $request)
    {
        $originalPrice = $request->price/$request->qty;
        DB::table('customers_basket')->where('customers_basket_id', '=', $request->busket_id)->update(
            [
                'customers_basket_quantity' => $request->qty - 1,
                'final_price' => $originalPrice*($request->qty - 1),
            ]);

        $basket = DB::table('customers_basket')->where([
            ['customers_basket_id', '=', $request->busket_id],
        ])->select('customers_basket_quantity','final_price', 'products_id')->get();
        return response()->json($basket);
    }

}
