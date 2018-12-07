
package com.daycomole;

import java.io.InputStreamReader;
import java.io.Reader;
import java.net.URL;
import java.net.URLConnection;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;
import java.security.cert.X509Certificate;

import java.io.InputStream;
import java.util.Properties;

public class WebProperties {

		public static Properties download(String propertiesUrl) throws Exception {

			// http://www.rgagnon.com/javadetails/java-fix-certificate-problem-in-HTTPS.html

  	  /*
    	 *  fix for
	     *    Exception in thread "main" javax.net.ssl.SSLHandshakeException:
  	   *       sun.security.validator.ValidatorException:
    	 *           PKIX path building failed: sun.security.provider.certpath.SunCertPathBuilderException:
	     *               unable to find valid certification path to requested target
  	   */
			TrustManager[] trustAllCerts = new TrustManager[] {
      	new X509TrustManager() {
        	public java.security.cert.X509Certificate[] getAcceptedIssuers() {
            return null;
          }

          public void checkClientTrusted(X509Certificate[] certs, String authType) {  }

          public void checkServerTrusted(X509Certificate[] certs, String authType) {  }

				}
			};

			SSLContext sc = SSLContext.getInstance("SSL");
			sc.init(null, trustAllCerts, new java.security.SecureRandom());
			HttpsURLConnection.setDefaultSSLSocketFactory(sc.getSocketFactory());

			// Create all-trusting host name verifier
			HostnameVerifier allHostsValid = new HostnameVerifier() {
				public boolean verify(String hostname, SSLSession session) {
         	return true;
				}
			};

			// Install the all-trusting host verifier
			HttpsURLConnection.setDefaultHostnameVerifier(allHostsValid);
			/*
			* end of the fix
			*/
				
      URL url = new URL(propertiesUrl);
      URLConnection conn = url.openConnection();
			InputStream input = conn.getInputStream();
			Properties prop = new Properties();
			prop.load(input);
			input.close();
			return prop;
	}
}


