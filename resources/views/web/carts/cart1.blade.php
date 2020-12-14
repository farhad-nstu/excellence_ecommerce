<div class="container-fuild">
  <nav aria-label="breadcrumb">
      <div class="container">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ URL::to('/')}}">@lang('website.Home')</a></li>
            <li class="breadcrumb-item active" aria-current="page">@lang('website.Shopping cart')</li>
          </ol>
      </div> 
    </nav>
</div>

<section class="pro-content">
  <div class="container">
    <div class="page-heading-title">
        <h2>@lang('website.Shopping cart')</h2>           
    </div>
  </div>

<section class=" cart-content">
      <div class="container">
      <div class="row">

      <div class="col-12 col-sm-12 cart-area cart-page-one">
        @if(session()->has('message'))
           <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           </div>
       @endif
       @if(session::get('out_of_stock') == 1)
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
               This Product is out of stock.
               <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
      @endif
        <div class="row">

          <div class="col-12 col-lg-9">
            <form method='POST' id="update_cart_form" action='{{ URL::to('/updateCart')}}' >
              <div class="error-messages" style="display:none; color: red; font-size: 25px; font-weight: bold;"></div>
            <table class="table top-table">
              <?php
                $price = 0;
               ?>
              @foreach( $result['cart'] as $products)

              <?php $price = $price + $products->final_price; ?>
             
              <tbody  @if(session::get('out_of_stock') == 1 and session::get('out_of_stock_product') == $products->products_id)style="	box-shadow: 0 20px 50px rgba(0,0,0,.5); border:2px solid #FF9999;"@endif>

                  <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                  <input type="hidden" name="cart" value="{{$products->customers_basket_id}}">

                  @if($products->products_options_values)
                  <input type="hidden" name="choice_opotion" value="{{ $products->products_options_values }}">
                  @else
                  <input type="hidden" name="choice_opotion" value="42">
                  @endif

                  <tr class="d-flex">
                    <td class="col-12 col-md-3" >
                      <a href="{{ URL::to('/product-detail/'.$products->products_slug)}}" class="cart-thumb">
                        <img class="img-fluid" src="{{asset('').$products->image_path}}" alt="{{$products->products_name}}"/>
                        </a>
                    </td>
                      <td class="col-12 col-md-4 item-detail-left">
                        <div class="item-detail">
                            <span>
                              @foreach($products->categories as $key=>$category)
                                  {{$category->categories_name}}@if(++$key === count($products->categories)) @else, @endif
                              @endforeach 
                            </span>
                            <h4>{{$products->products_name}}
                            </h4>

                            @if($products->products_options_values)
                            <div class="item-attributes">
                            {{ $products->products_options_values }}
                            </div>
                            @else
                            <div class="item-attributes">
                            42
                            </div>
                            @endif

                            <div class="item-controls">
                                <a href="{{ url('/editcart/'.$products->customers_basket_id.'/'.$products->products_slug)}}"  class="btn" >
                                  <span class="fas fa-pencil-alt"></span>
                                </a>

                                <a href="{{ URL::to('/deleteCart?id='.$products->customers_basket_id)}}"  class="btn" >
                                  <span class="fas fa-times"></span>
                              </a>
                            </div>                          
                          </div>                        

                      </td>
                      <?php
                      if(!empty($products->discount_price)){
                          $discount_price = $products->discount_price * session('currency_value');
                      }
                      if(!empty($products->final_price)){
                        $flash_price = $products->final_price * session('currency_value');
                      }
                      $orignal_price = $products->price * session('currency_value');


                       if(!empty($products->discount_price)){

                        if(($orignal_price+0)>0){
                          $discounted_price = $orignal_price-$discount_price;
                          $discount_percentage = $discounted_price/$orignal_price*100;
                       }else{
                         $discount_percentage = 0;
                         $discounted_price = 0;
                     }
                   }

                   ?>
                  <td class="item-price col-12 col-md-2 total_prc" id="total_prc{{ $products->products_id }}">
                    @if(!empty($products->final_price))
                    {{Session::get('symbol_left')}}{{$flash_price+0}}{{Session::get('symbol_right')}}
                    @elseif(!empty($products->discount_price))
                    {{Session::get('symbol_left')}}{{$discount_price+0}}{{Session::get('symbol_right')}}
                    <span> {{Session::get('symbol_left')}}{{$orignal_price+0}}{{Session::get('symbol_right')}}</span>
                    @else
                    {{Session::get('symbol_left')}}{{$orignal_price+0}}{{Session::get('symbol_right')}}
                    @endif
                  </td>
                   
                    <td class="col-12 col-md-2 Qty">                          
                        <div class="input-group item-quantity"> 
                            <input name="quantity" type="text" id="{{ $products->products_id }}" readonly value="{{$products->customers_basket_quantity}}" class="form-control qty" min="{{$products->min_order}}" max="{{$products->max_order}}">

                            <span class="input-group-btn">
                              <a href="javascript:location.reload(true)"><button type="button" class="btn" id="incBtn">
                                <i class="fas fa-plus" id="incB" onclick="increase(`{{ $products->customers_basket_quantity }}`, `{{ $products->final_price }}`, `{{ $products->customers_basket_id }}`, `{{ $products->products_id }}`)"></i>
                              </button></a>

                              <a href="javascript:location.reload(true)"><button type="button" class="btn">
                                <i class="fas fa-minus" onclick="decrease(`{{ $products->customers_basket_quantity }}`, `{{ $products->final_price }}`, `{{ $products->customers_basket_id }}`)"></i>
                              </button></a>
                            </span>
                        </div>
                    </td>

                  </tr>
              </tbody>
              @endforeach
            </table>
          </form>
            @if(!empty(session('coupon')))
              <div class="form-group">
                    @foreach(session('coupon') as $coupons_show)

                        <div class="alert alert-success">
                            <a href="{{ URL::to('/removeCoupon/'.$coupons_show->coupans_id)}}" class="close"><span aria-hidden="true">&times;</span></a>
                          @lang('website.Coupon Applied') {{$coupons_show->code}}.@lang('website.If you do note want to apply this coupon just click cross button of this alert.')
                        </div>

                    @endforeach
                </div>
            @endif
            <div class="col-12 col-lg-12 mb-4">
              <div class="row justify-content-between click-btn">
                <div class="col-12 col-lg-4">
                  <form id="apply_coupon" class="form-validate">
                    <div class="row">
                        <div class="input-group">
                            <input type="text" name="coupon_code" class="form-control" id="coupon_code" placeholder="Coupon Code" aria-label="Coupon Code" aria-describedby="coupon-code">

                            <div class="">
                              <button class="btn btn-secondary swipe-to-top" type="submit" id="coupon-code">@lang('website.APPLY')</button>
                            </div>
                        </div>
                        <div id="coupon_error" class="help-block" style="display: none;color:red;"></div>
                        <div  id="coupon_require_error" class="help-block" style="display: none;color:red;">@lang('website.Please enter a valid coupon code')</div>
                    </div>
                 </form>
                </div>
                <div class="col-12 col-lg-7 align-right">
                  <div class="row">
                    <a  href="{{ URL::to('/shop')}}" class="btn btn-secondary swipe-to-top">@lang('website.Back To Shopping')</a>
                    <!-- <button class="btn btn-light swipe-to-top" id="update_cart">@lang('website.Update Cart')</button> -->
                  </div>
               
                </div>
               
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-3">
            <table class="table right-table">
              <thead>
                <tr>
                  <th scope="col" colspan="2" align="center">@lang('website.Order Summary')</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">@lang('website.SubTotal')</th>
                  <td align="right" class="total_price">
                    @php

                    if(!empty(session('coupon_discount'))){
                      $coupon_amount = session('currency_value') * session('coupon_discount');  
                    }else{
                      $coupon_amount = 0;
                    }

                    @endphp
                    {{Session::get('symbol_left')}}{{session('currency_value') * $price}}{{Session::get('symbol_right')}}
                  </td>
                </tr>
                <tr>
                  <th scope="row">@lang('website.Discount(Coupon)')</th>
                  <td align="right">{{Session::get('symbol_left')}}{{number_format((float)$coupon_amount, 2, '.', '')+0}}{{Session::get('symbol_right')}}</td>
                </tr>
                <tr class="item-price">
                  <th scope="row">@lang('website.Total')</th>
                  <td align="right" class="total_price" id="cartPrice">{{Session::get('symbol_left')}}{{session('currency_value') * $price-number_format((float)$coupon_amount, 2, '.', '')}}{{Session::get('symbol_right')}}</td>
                </tr>
              </tbody>
            </table>
            <a href="{{ URL::to('/checkout')}}" class="btn btn-secondary m-btn col-12 swipe-to-top">@lang('website.proceedToCheckout')</a>
          </div>
        </div>
      </div>
    </div>

    </div>
  </section>
</section>



<script type="">   

    function increase(orgnlQty, fnlPrice, busket_id, id){

      
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

        $.ajax({
                type: "post",
                url : '{{url("web/increase")}}',
                data: {
                        qty: orgnlQty,
                        price: fnlPrice,
                        busket_id: busket_id
                    },
                success:function(data) {
                  console.log(data);
                  document.getElementById(data[0]['products_id']).value = data[0]['customers_basket_quantity'];
                  var str = data[0]['final_price'];
                  var n = str.indexOf(".");
                  var sub_str_price = str.substr(0, n);
                  document.getElementById("total_prc"+data[0]['products_id']).innerHTML = "৳"+sub_str_price;
                }
            });
    }

    function decrease(orgnlQty, fnlPrice, busket_id){

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

        $.ajax({
                type: "post",
                url : '{{url("web/decrease")}}',
                data: {
                        qty: orgnlQty,
                        price: fnlPrice,
                        busket_id: busket_id
                    },
                success:function(data) {
                  console.log(data);
                  document.getElementById(data[0]['products_id']).value = data[0]['customers_basket_quantity'];
                  var strr = data[0]['final_price'];
                  var num = strr.indexOf(".");
                  var sub_strs_price = strr.substr(0, num);
                  document.getElementById("total_prc"+data[0]['products_id']).innerHTML = "৳"+sub_strs_price;
                }
            });
    }

    $(document).ready(function(){
      let cartPrice = document.getElementById('cartPrice').innerHTML;
      let subStrTaxcartPrice = cartPrice.substr(1);
      $.ajax({
               url: '{{url("web/cartPrice/store")}}',
               data: { cartPrice: subStrTaxcartPrice }
          });
    });

</script>

