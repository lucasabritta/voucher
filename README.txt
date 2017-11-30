This task was created using mysql and php and tested using XAMPP.
The DB was created into phpmyadmin and the sql to create the tables can be found under sql/voucher.sql.
A postman collection was created to test all functionality from scratch. The postman collection and environment can be found under postman folder.
To develop this system 5 main functions was created : createSpecialOffer, createRecipient, generateVoucherCode, validateVoucherCode and getUserValidVoucher.
The createSpecialOffer function expects name and discount as parameter, this function try to insert into special_offer table the value received as parameter.
The createRecipient function expects name and email as parameter, this function try to insert into recipient table the value received as parameter.
The generateVoucherCode function expects specialOfferName and expirationDate as parameter, this function create the a unique voucher code for each recipient in the DB.
The validateVoucherCode function expects name and email as parameter, this function validates the voucher code, looking if the code and email match, if the expiration date is in the future and if this voucher code was not redeemed yet. In Case it is valid, return the Percentage Discount and set the date of usage in the DB.
The getUserValidVoucher function expects a email as parameter, this function return all valid voucher codes with the name of the special offer of the given user.