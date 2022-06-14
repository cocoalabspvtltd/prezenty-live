<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>PDF</title>
</head>

<body>
    <div class="row mt-5" style="padding: 10px;  --bs-gutter-x: 0rem !important;
    --bs-gutter-x: 0rem;
    --bs-gutter-y: 0 !important;">
        <div class="col-lg-12 col-12 col-xl-7 col-sm-11 mx-auto" style="    border-style: groove;
        padding: 20px;">
          <div class="row " style="  --bs-gutter-x: 0rem !important;
          --bs-gutter-x: 0rem;
          --bs-gutter-y: 0 !important;">
            <div class="col-lg-2 col-xl-3 col-3 endofText" style=" text-align: center;
            justify-content: end;
            flex-direction: column-reverse;
            display: flex;
            flex-direction: column;">
              <img
                src="https://prezenty.in/favicon.png"
                style="width: 65px;"
              />
            </div>

          </div>
        
          <div class="row" style="  --bs-gutter-x: 0rem !important;
          --bs-gutter-x: 0rem;
          --bs-gutter-y: 0 !important;">
            <div class="col-lg-12 col-xl-12 col-12 mt-3">
              <h3 class="jt-text"  style="    text-align: justify;
              ">
                <b
                  >Thank you for your payment
                </b>
              </h3><br>
              <p>Dear <?php print_r($mailData[0]['name']); ?>,<br>
              Details of your transactions are given below</p>
            </div>
          </div>
          <hr>
          <?php
            $i=1;    
          foreach ($mailData as $key => $value) {
              
            ?>
            <h4><b><?php echo $i++; ?>. Order Details of Voucher :<?php echo $value['voucher'] ?></b></h4>
            <hr>            
          <div class="row " style="  --bs-gutter-x: 0rem !important;
          --bs-gutter-x: 0rem;
          --bs-gutter-y: 0 !important;">
                <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                        <span>Address</span>
              </div>
              <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                <span><b><?php echo $value['address']; ?></b></span>
              </div>
              <hr>              
                <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                        <span>Mobile Number</span>
              </div>
              <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                <span><b><?php echo $value['phone']; ?></b></span>
              </div>
              <hr>
              <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                <span>Amount Paid</span>
                </div>
                <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                  <span><b><?php echo $value['amountR'] ?></b></span>
                </div>
                <hr>
                <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                  <span>Date and Time</span>
                  </div>
                  <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                    <span><b><?php echo $value['date'] ?></b></span>
                  </div>
                  <hr>
                  <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                    <span>Voucher Name</span>
                    </div>
                    <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                      <span><b><?php echo $value['voucher'] ?> </b></span>
                    </div>
                    <hr>
                    <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                      <span>Order Id</span>
                      </div>
                      <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                        <span><b><?php echo $value['id'] ?></b></span>
                      </div>
                      <hr>
                      <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                        <span>Payment Status</span>
                        </div>
                        <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                          <span><b>SUCCESS</b></span>
                        </div>
                      <hr>
                      <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                        <span>Order Status</span>
                        </div>
                        <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                          <span><b><?php echo $value['status']; ?></b></span>
                        </div> 
                         <hr>
                         
                    <?php } ?>    
                      <div class="col-lg-8 col-xl-8 col-6 col-md-8">
                          <br>
                        <span>Note:</span>
                        </div>
                        <div class="col-lg-4 col-xl-4 col-6 col-md-4">
                          <span><b>
                              Email:support@prezenty.in<br>
                              Phone:7025261673,7025261674<br>
                              If Voucher is Not Received with in 30Minutes Please Contact Our Customer Care! Have A Good Day</b></span>
                        </div>                         
              </div>
                                                  </div>
                        </div>
                    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
</body>

</html>
