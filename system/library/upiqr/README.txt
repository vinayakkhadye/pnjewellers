About:
------
[UPI-Payment] is a Payment Module to Integrate
UPI - [ Unified Payments Interface ] A Service From NPCI - [ National Payments Corporation Of India ]
as a Payment Gateway Method in OPENCART - 3.0.2.0 - 3.0.3.0 - 3.0.3.1 - 3.0.3.2 - 3.0.3.3

Software:
---------
* A Self Contained Library [Open Source QR Code Library].
* No External Data Transfer.
* 100% New Files , No File OVERWRITE or REMOVAL.

Features:
---------

Customer On Checkout
* UPI-QR is shown for selected time.
* Customer Scan And Pay By UPI-APP.
* Customer can input UPI Transaction Details.
* Order History is Updated.
* A Simple Text E-Mail with UPI-QR as Attachment is Sent To Customer.
* Auto Redirect To Success Page On Update of Order Process Status By Administrator Or By Programmed Script Or UPI-QR Show Time Out.

Administrator Order Add / Edit
* UPI Payment Method Available. 
* UPI-QR is sent to Customer E-Mail as Attachment.
 
Optional Feature:
-----------------
* UPI-QR as Attachment with ORDER E-MAIL is Sent To Customer.
* Administrator Can Enable This Feature In Extensions / Events.

Installation:
-------------
* Extract ZIP File and Upload All Folders Under [upload] Directory to your Server.

Directory/File Permissions:
---------------------------
* UPI-QR Images are Stored in "image/upiqrimage" i.e. [DIR_IMAGE."upiqrimage"].
* Set WRITE Permissions on "image/upiqrimage" i.e. [DIR_IMAGE."upiqrimage"].

Events:
-------
* Three Events are Created in Administrator Login under Extensions / Events.

* Event Name : [get_upi_qr_code]
* Event Status : ENABLED

* Event Name : [mail_order_add_upipayment]
* Event Status : DISABLED

* Event Name : [mail_order_add_upipayment_qr]
* Event Status : ENABLED

* OpenCart Default Event : [mail_order_add]
* Event Status : ENABLED

Enable Optional Feature:
------------------------
* To Send UPI-QR as Attachment with ORDER E-MAIL
* Go To Events Under Extensions in Administrator Login.

Method 1:
---------
* Switch The Event Statuses
	[mail_order_add] Event Status : DISABLED
	[mail_order_add_upipayment] Event Status : ENABLED
        [mail_order_add_upipayment_qr] Event Status : DISABLED

Method 2:
---------
* If Your OpenCart Has Customised Order E-Mail
OR
* Wish To Continue Using Default Order E-Mail

	* Switch The Event Statuses
		[mail_order_add] Event Status : ENABLED
		[mail_order_add_upipayment] Event Status : DISABLED
                [mail_order_add_upipayment_qr] Event Status : DISABLED

1. Open File :

    [catalog/controller/mail/order.php]
    OR
    Your Custom Mail Order File

    : For Editing

2. Go To [public function add($order_info, $order_status_id, $comment, $notify)]

3. Move to end of function and find the line
	$mail->send();

4. Replace The Said Line With The Following Code

                // UPI QR IMAGE ATTACHMENT
                $upiqrimage = DIR_IMAGE."upiqrimage/".trim($order_info['order_id']).".png";
                if((trim($order_info['payment_code']) === "upipayment") && (file_exists($upiqrimage))){
                    $mail->addAttachment($upiqrimage);
                }
                $mail->send();
                if(file_exists($upiqrimage)){
                    $imagearchivedir = DIR_IMAGE."upiqrimage/archive";
                    if (!file_exists($imagearchivedir)) {
                        mkdir($imagearchivedir, 0755, true);
                    }
                    $imagepath = $imagearchivedir.'/'. basename($upiqrimage);
                    rename($upiqrimage, $imagepath);
                }
                // UPI QR IMAGE ATTACHMENT

5. The Result Code be.

	public function add($order_info, $order_status_id, $comment, $notify) {
	|
	|
	|
	|
		$mail->setHtml($this->load->view('mail/order_add', $data));
		
                // UPI QR IMAGE ATTACHMENT
                $upiqrimage = DIR_IMAGE."upiqrimage/".trim($order_info['order_id']).".png";
                if((trim($order_info['payment_code']) === "upipayment") && (file_exists($upiqrimage))){
                    $mail->addAttachment($upiqrimage);
                }
                $mail->send();
                if(file_exists($upiqrimage)){
                    $imagearchivedir = DIR_IMAGE."upiqrimage/archive";
                    if (!file_exists($imagearchivedir)) {
                        mkdir($imagearchivedir, 0755, true);
                    }
                    $imagepath = $imagearchivedir.'/'. basename($upiqrimage);
                    rename($upiqrimage, $imagepath);
                }
                // UPI QR IMAGE ATTACHMENT

	}

SUPPORT:
--------
* FOR PAID SERVICE OF SUPPORT AND CUSTOM INSTALLATION KINDLY CONTACT US

M/s. ABCD PAYMENT SERVICES

# 4-1-71/416, SAI DURGA GARDENS,
NACHARAM MAIN ROAD,
MEDCHAL-MALKAJGIRI DISTRICT,
TELANGANA STATE, INDIA.
PIN: 500076.

E-MAIL: admin@abcd-pay.com
Mobile: 0091-0-8106877688.

TERMS OF USE:
-------------
* The End User Can Use This Code In Its Original Format or Modify To Fit Their Requirement.

PAY TO DEVELOPER:
-----------------

UPI-ID	: 8106877688@upi
PAYEE	: ABCD PAYMENT SERVICES
AMOUNT	: INR. 250
REMARKS	: UPIQR