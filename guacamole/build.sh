#!/bin/bash

export JAVA_HOME="/opt/jdk1.7.0_79"
export MAVEN_HOME="/opt/apache-maven-3.3.3"
export TOMCAT_HOME="/opt/apache-tomcat-7.0.62"
export TOMCAT_URL=http://10.0.1.235:8080

export PATH="$JAVA_HOME/bin:$MAVEN_HOME/bin:$TOMCAT_HOME/bin:$PATH"

sudo $TOMCAT_HOME/bin/shutdown.sh


sudo rm -rf $TOMCAT_HOME/webapps/daycomole*
sudo rm -rf $TOMCAT_HOME/work/Catalina/localhost/daycomole*

mvn package 

sudo cp target/daycomole-0.1.0.war $TOMCAT_HOME/webapps/

sudo $TOMCAT_HOME/bin/startup.sh

echo $TOMCAT_URL/daycomole-0.1.0

