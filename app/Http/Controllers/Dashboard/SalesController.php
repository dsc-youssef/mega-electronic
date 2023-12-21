<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Sale\CreateSaleRequest;
use App\Http\Requests\Dashboard\Sale\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleHasProduct;
use App\Traits\Requests;
use Inertia\Inertia;

class SalesController extends Controller
{

  use Requests;

  /**
   * Display sales page.
   * @return \Inertia\Response
   */
  public function show(): \Inertia\Response
  {
    $sales = Sale::with('owner', 'customer')
      ->get();

    return Inertia::render(
      "Dashboard/Sales/index",
      [
        'sales' => $sales,
        'pageWords' => __('pages/dashboard/sales')
      ]
    );
  } // End Method

  /**
   * Display sale page.
   * @return \Inertia\Response
   */
  public function sale(): \Inertia\Response
  {
    $products = Product::all();
    $customers = Customer::all();
    return Inertia::render(
      "Dashboard/Sale/index",
      [
        'products' => $products,
        'customers' => $customers,
        'pageWords' => __('pages/dashboard/sales')
      ]
    );
  }

  /**
   * Display update sale page.
   * @param int $id
   * @return \Inertia\Response
   */
  public function updateSale(int $id): \Inertia\Response
  {
    $sale = Sale::with('products', 'customer', 'owner')->findOrFail($id);
    $customers = Customer::all();
    return Inertia::render(
      'Dashboard/UpdateSale/index',
      [
        'sale' => $sale,
        'customers' => $customers,
        'pageWords' => __('pages/dashboard/sales')
      ]
    );
  }

  /**
   * Create sale.
   * @param CreateSaleRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(CreateSaleRequest $request): \Illuminate\Http\RedirectResponse
  {
    $products = (array) $request->products;
    $productsAmount = 0;

    # Validate customer.
    if (!$this->validateRequestCustomer($request->customer_id))
      return back()->with($this->createRequestNotification(__("pages/dashboard/sales.invalid_selected_customer"), 'danger'));

    # Validate products.
    if (!$this->validateRequestProducts($products))
      return back()->with($this->createRequestNotification(__("pages/dashboard/sales.invalid_selected_products"), 'danger'));

    # Get products total amount.
    $productsAmount = $this->getRequestProductsTotalAmount($products);

    $sale = Sale::create([
      'method' => $request->method,
      'amount' => $productsAmount,
      'discount' => $request->discount,
      'created_by' => $request->user()->id,
      'customer_id' => Customer::find($request->customer_id)->id
    ]);
    $this->createSaleProducts($sale->id, $products);

    return back()->with($this->createRequestNotification(__("pages/dashboard/sales.sale_created_successfully"), 'success'));
  } // End Method

  /**
   * Update sale.
   * @param UpdateSaleRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateSaleRequest $request): \Illuminate\Http\RedirectResponse
  {
    $sale = Sale::findOrFail($request->id);
    $products = (array) $request->products;
    $productsAmount = 0;

    # Validate products.
    if (!$this->validateRequestProducts($products))
      return back()->with($this->createRequestNotification(__("pages/dashboard/sales.invalid_selected_products"), 'danger'));

    # Validate customer.
    if (!$this->validateRequestCustomer($request->customer_id))
      return back()->with($this->createRequestNotification(__("pages/dashboard/sales.invalid_selected_customer"), 'danger'));

    # Get products total amount.
    $productsAmount = $this->getRequestProductsTotalAmount($products);

    # update sale details.
    $sale->method = $request->method;
    $sale->amount = $productsAmount;
    $sale->discount = $request->discount;
    $sale->customer_id = $request->customer_id;
    $sale->save();

    # Remove unregistered products.
    SaleHasProduct::where('sale_id', $sale->id)
      ->whereNotIn('product_id', array_column($products, 'id'))->delete();

    $this->createSaleProducts($sale->id, $products);

    return back()->with($this->createRequestNotification(__("pages/dashboard/sales.sale_updated_successfully"), 'success'));
  } // End Method

  /**
   * Delete sale.
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function delete(int $id): \Illuminate\Http\RedirectResponse
  {
    Sale::findOrFail($id)->delete();
    return back()->with($this->createRequestNotification(__("pages/dashboard/sales.sale_removed_successfully"), 'success'));
  } // End Method

  /**
   * ********************************************************************
   * Private Methods
   * ********************************************************************
   */

  /**
   * Validate request products.
   * NOTE: this method using only inside this controller.
   * @param array $products
   * @return bool
   */
  private function validateRequestProducts(array $products): bool
  {
    $result = true;
    for ($i = 0; $i < count($products); $i++) {
      if (!Product::find($products[$i]['id'])->exists()) {
        $result = false;
      }
    }
    return $result;
  } // End Method

  /**
   * Validate request customer.
   * NOTE: this method using only inside this controller.
   * @param int $customer_id
   * @return bool
   */
  private function validateRequestCustomer(int $customer_id): bool
  {
    return Customer::find($customer_id) !== null;
  } // End Method

  /**
   * Get request products total amount.
   * NOTE: this method using only inside this controller.
   * @param array $products
   * @return float
   */
  private function getRequestProductsTotalAmount(array $products): float
  {
    $productsTotalAmount = 0;
    foreach ($products as $product) {
      $productsTotalAmount += (float) Product::find($product['id'])->price * $product['quantity'];
    }
    return $productsTotalAmount;
  } // End Method

  /**
   * Create sale product.
   * NOTE: this method using only inside this controller.
   * @param int $sale_id
   * @param array $products
   * @return void
   */
  private function createSaleProducts(int $sale_id, array $products): void
  {
    foreach ($products as $product) {
      if (
        !SaleHasProduct::where('sale_id', $sale_id)
          ->where('product_id', $product['id'])
          ->exists()
      ) {
        SaleHasProduct::create([
          'product_id' => $product['id'],
          'quantity' => $product['quantity'],
          'sale_id' => $sale_id
        ]);
      }
    }
  } // End Method

}
