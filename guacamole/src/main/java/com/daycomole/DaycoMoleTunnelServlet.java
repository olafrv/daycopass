
package com.daycomole;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpSession;
import org.glyptodon.guacamole.GuacamoleException;
import org.glyptodon.guacamole.net.GuacamoleSocket;
import org.glyptodon.guacamole.net.SimpleGuacamoleTunnel;
import org.glyptodon.guacamole.net.*;
import org.glyptodon.guacamole.net.InetGuacamoleSocket;
import org.glyptodon.guacamole.protocol.ConfiguredGuacamoleSocket;
import org.glyptodon.guacamole.protocol.GuacamoleConfiguration;
import org.glyptodon.guacamole.servlet.GuacamoleHTTPTunnelServlet;
import org.glyptodon.guacamole.servlet.GuacamoleSession;

import java.io.FileInputStream;
import java.util.Properties;
import java.util.Set;
import java.util.Iterator;

public class DaycoMoleTunnelServlet
 extends GuacamoleHTTPTunnelServlet {

    @Override
    protected GuacamoleTunnel doConnect(HttpServletRequest request) throws GuacamoleException {

				// Read global properties				
				Properties settings = null;
				try{
					FileInputStream fis = new FileInputStream("/etc/guacamole/guacamole.properties");
					settings = new Properties();
					settings.load(fis);
					fis.close();
				}catch(Exception e){
					System.out.println(e);
					return null;
				}
			
				// Connection properties
				WebProperties web = new WebProperties();
				Properties properties = null;			
				try{
					String url = 
						settings.getProperty("daycopass-url")
						+ "&id=" + request.getParameter("id")
						+ "&token=" + request.getParameter("token");
					System.out.println(url);
					properties = web.download(url);
				}catch(Exception e){
					System.out.println(e);
					return null;
				}

			
        // Create our configuration
        GuacamoleConfiguration config = new GuacamoleConfiguration();

				// First set the protocol
			  config.setProtocol(request.getParameter("protocol"));

				// Then set parameters (from daycopass request)
			  config.setParameter("port", request.getParameter("port"));
			  config.setParameter("hostname", request.getParameter("hostname"));

				// Then set the parameters (from daycopass credential)
				Set names = properties.keySet();
				Iterator itr = names.iterator();
				while(itr.hasNext()) {
					String name = (String) itr.next();
					String value = properties.getProperty(name);
					if (name.equals("usuario")) {
						config.setParameter("username", value);
					}
					if (name.equals("clave")){
						config.setParameter("password", value);
					}
				} 

				config.setParameter("ignore-cert", "true");
				config.setParameter("disable-audio", "true");
				config.setParameter("enable-sftp","true");
				// config.setParameter("domain", "DAYCOHOST"); // Windows (Optional)
				config.setParameter("security","nla"); //windows 2012 (Optional)
	
        // Connect to guacd - everything is hard-coded here.
        GuacamoleSocket socket = new ConfiguredGuacamoleSocket(
                new InetGuacamoleSocket(
									settings.getProperty("guacd-hostname"),
									Integer.valueOf(settings.getProperty("guacd-port"))
								),
                config
        );

        // Establish the tunnel using the connected socket
        GuacamoleTunnel tunnel = new SimpleGuacamoleTunnel(socket);

        // Attach tunnel to session
        HttpSession httpSession = request.getSession(true);
        GuacamoleSession session = new GuacamoleSession(httpSession);
        session.attachTunnel(tunnel);

        // Return pre-attached tunnel
        return tunnel;

    }

}

