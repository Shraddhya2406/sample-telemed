You are a senior Laravel 11 backend developer.

We are working on a telemedicine project called **sample-telemed**.

We already have:
- Patient dashboard
- Medicine store
- Cart system
- Order system

Now we want to integrate **Sticky CRM payment API** into the checkout flow.

---

# 🎯 GOAL

When a patient clicks **Checkout**:

1. Show credit card form
2. Validate card details
3. Send payment request to Sticky CRM API
4. Handle response (success / failure)
5. Create order ONLY if payment is successful
6. Clear cart after success

---

# 🔐 STICKY CRM API DETAILS

Sticky CRM uses **form-data POST request (not JSON)**.

Use Laravel HTTP client:

Http::asForm()->post()

---

## Required Fields (example)

username
password
campaignId
creditCardNumber
expirationDate (MMYY format)
cvv
firstName
lastName
email
phone
address1
city
state
zip
country

---

# 🧱 IMPLEMENTATION STEPS

---

## 1️⃣ Create Payment Service

File:
app/Services/StickyPaymentService.php

Responsibilities:
- Send request to Sticky API
- Return response
- Handle errors

---

## 2️⃣ Service Method

Method:
processPayment(array $data)

Use:

Http::asForm()->post(config('services.sticky.url'), [...])

---

## 3️⃣ Checkout Form

File:
resources/views/patient/checkout.blade.php

Fields:
- Card Number
- Expiry Month
- Expiry Year
- CVV
- Billing Name
- Email

Submit to:
patient.placeOrder

---

## 4️⃣ Controller Update

File:
Patient/OrderController.php

Method:
placeOrder(Request $request)

Steps:

- Validate request
- Get cart items
- Calculate total
- Format expiry (MMYY)
- Call StickyPaymentService
- If success:
    - Create order
    - Create order items
    - Clear cart
    - Redirect to orders page
- If failure:
    - Show error message

---

## 5️⃣ Sample Payment Call

$response = Http::asForm()->post(...)

Check:

if ($response['responseCode'] == 100) → success

Else → failure

---

## 6️⃣ Error Handling

- Show user-friendly error
- Log API response

---

# 🔐 SECURITY RULES

- Never store card details in DB
- Do not log sensitive data
- Validate all inputs

---

# 🎨 UI REQUIREMENTS

Checkout page must:
- Be clean and modern
- Show total amount
- Have "Pay Now" button
- Show loading state on submit

---

# 📌 ROUTES

POST /patient/place-order

Middleware:
auth
role:patient

---

# 🧠 BEST PRACTICES

- Use service class for API calls
- Keep controller clean
- Use transactions for order creation

---

# 📦 OUTPUT REQUIRED

Generate:

1. StickyPaymentService class
2. Updated OrderController@placeOrder
3. Checkout Blade form
4. Validation rules
5. Proper response handling

Follow Laravel best practices.