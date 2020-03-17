 - **Certificate Authentication:**
    1. Use the Merchant portal to download the a test.crt and test.key files for your test Merchant. Make sure that your Merchant is configured to use SSL certificate authentication.
    2. Once you have the crt and key files,use openssl to generate the .p12 file
            -  openssl pkcs12 -export -inkey test.key -in test.crt certif -out cert.p12  
    3. Set the env variables: 
      - export PKI_BASE_URL=*INSERT_YOUR_GATEWAY_PKI_BASE_URL_HERE*  GATEWAY_SSL_CERT_PATH=*INSERT_YOUR_SSL_CERT_PATH_HERE* VERIFY_PEER=*TRUE* VERIFY_HOST=*2*
    
        1. **GATEWAY_SSL_CERT_PATH**(which is used by  <certificatePath> in settings.php) to point to your cert.p12 file
        2. **VERIFY_PEER** : TRUE or FALSE
        3. **VERIFY_HOST** : 0 or 1 or 2 
        4. **PKI_BASE_URL** : Gateway URL to be used for Certificate authentication
        
    **IMPORTANT NOTE:** 
            
        **PRODUCTION MODE** : Ensure that  **VERIFY_PEER=TRUE and VERIFY_HOST=1 or 2** (in settings.php) in Production.
            
        **DEVELOPMENT MODE**, run :  
              _export VERIFY_PEER=FALSE  VERIFY_HOST=0_ 
                _The test certificate is self signed and these flags are not really needed in Development mode._
            
        You can find more details about these options here: http://php.net/manual/en/function.curl-setopt.php
    
        