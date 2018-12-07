if (undefined == LDAPCHPW_LIB) {
  var LDAPCHPW_LIB = new Object();
}

LDAPCHPW_LIB.longitudMinima = 8;
LDAPCHPW_LIB.longitudMaxima = 16;

LDAPCHPW_LIB.caracteres = new Array(
	"ABCDEFGHIJKLMNOPQRSTUVWXYZ", 
	"abcdefghijklmnopqrstuvwxyz",
	"0123456789",               
	"~!@#$%^*()_+-={}[]|:;,./<>?" 
);

LDAPCHPW_LIB.verificarPassword = function (text) 
{
	var longitud = text.length;
	var caracteresOk = new Array(0,0,0,0);
	for(i=0;i<longitud;i++){ //Todas las letras del texto
		for(j=0;j<4;j++){ //Todos los tipos de caracteres
			for(k=0;k<this.caracteres[j].length;k++){ //Todos los caracteres de un tipo
				if (text.substring(i,i+1)==this.caracteres[j].substring(k,k+1)){
					(caracteresOk[j])++;	
				}
			}
		}
	}
	return (caracteresOk[0]>0 || caracteresOk[1]>0) 
		&& caracteresOk[2]>0 && caracteresOk[3]>0 
			&& longitud>=this.longitudMinima
				&& longitud<=this.longitudMaxima;
}

LDAPCHPW_LIB.generarPassword = function()
{
   var pass = "";
   var x = 0;
   var indiceArreglo = 0;
   var indiceCaracter = 0;
   for(x=0;x<this.longitudMinima;x++){
     if (x==0) indiceArreglo = 0;
     if (x>=1 && x<=4) indiceArreglo = 1;
     if (x>=5 && x<=6) indiceArreglo = 2;
     if (x==7) indiceArreglo=3;
     indiceCaracter = Math.floor(Math.random() * this.caracteres[indiceArreglo].length);
     pass += this.caracteres[indiceArreglo].charAt(indiceCaracter);
   }
   return pass;
}

