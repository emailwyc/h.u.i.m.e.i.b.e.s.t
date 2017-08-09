/* All of the Strophe globals are defined in this special function below so
 * that references to the globals become closures.  This will ensure that
 * on page reload, these references will still be available to callbacks
 * that are still executing.
 */

/* jshint ignore:start */
(function (callback) {
/* jshint ignore:end */

// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com

var Base64 = (function () {
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    var obj = {
        /**
         * Encodes a string in base64
         * @param {String} input The string to encode in base64.
         */
        encode: function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            do {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc2 = ((chr1 & 3) << 4);
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) +
                    keyStr.charAt(enc3) + keyStr.charAt(enc4);
            } while (i < input.length);

            return output;
        },

        /**
         * Decodes a base64 string.
         * @param {String} input The string to decode.
         */
        decode: function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            do {
                enc1 = keyStr.indexOf(input.charAt(i++));
                enc2 = keyStr.indexOf(input.charAt(i++));
                enc3 = keyStr.indexOf(input.charAt(i++));
                enc4 = keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }
            } while (i < input.length);

            return output;
        }
    };

    return obj;
})();

/*
 * A JavaScript implementation of the Secure Hash Algorithm, SHA-1, as defined
 * in FIPS PUB 180-1
 * Version 2.1a Copyright Paul Johnston 2000 - 2002.
 * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
 * Distributed under the BSD License
 * See http://pajhome.org.uk/crypt/md5 for details.
 */

/* Some functions and variables have been stripped for use with Strophe */

/*
 * These are the functions you'll usually want to call
 * They take string arguments and return either hex or base-64 encoded strings
 */
function b64_sha1(s){return binb2b64(core_sha1(str2binb(s),s.length * 8));}
function str_sha1(s){return binb2str(core_sha1(str2binb(s),s.length * 8));}
function b64_hmac_sha1(key, data){ return binb2b64(core_hmac_sha1(key, data));}
function str_hmac_sha1(key, data){ return binb2str(core_hmac_sha1(key, data));}

/*
 * Calculate the SHA-1 of an array of big-endian words, and a bit length
 */
function core_sha1(x, len)
{
  /* append padding */
  x[len >> 5] |= 0x80 << (24 - len % 32);
  x[((len + 64 >> 9) << 4) + 15] = len;

  var w = new Array(80);
  var a =  1732584193;
  var b = -271733879;
  var c = -1732584194;
  var d =  271733878;
  var e = -1009589776;

  var i, j, t, olda, oldb, oldc, oldd, olde;
  for (i = 0; i < x.length; i += 16)
  {
    olda = a;
    oldb = b;
    oldc = c;
    oldd = d;
    olde = e;

    for (j = 0; j < 80; j++)
    {
      if (j < 16) { w[j] = x[i + j]; }
      else { w[j] = rol(w[j-3] ^ w[j-8] ^ w[j-14] ^ w[j-16], 1); }
      t = safe_add(safe_add(rol(a, 5), sha1_ft(j, b, c, d)),
                       safe_add(safe_add(e, w[j]), sha1_kt(j)));
      e = d;
      d = c;
      c = rol(b, 30);
      b = a;
      a = t;
    }

    a = safe_add(a, olda);
    b = safe_add(b, oldb);
    c = safe_add(c, oldc);
    d = safe_add(d, oldd);
    e = safe_add(e, olde);
  }
  return [a, b, c, d, e];
}

/*
 * Perform the appropriate triplet combination function for the current
 * iteration
 */
function sha1_ft(t, b, c, d)
{
  if (t < 20) { return (b & c) | ((~b) & d); }
  if (t < 40) { return b ^ c ^ d; }
  if (t < 60) { return (b & c) | (b & d) | (c & d); }
  return b ^ c ^ d;
}

/*
 * Determine the appropriate additive constant for the current iteration
 */
function sha1_kt(t)
{
  return (t < 20) ?  1518500249 : (t < 40) ?  1859775393 :
         (t < 60) ? -1894007588 : -899497514;
}

/*
 * Calculate the HMAC-SHA1 of a key and some data
 */
function core_hmac_sha1(key, data)
{
  var bkey = str2binb(key);
  if (bkey.length > 16) { bkey = core_sha1(bkey, key.length * 8); }

  var ipad = new Array(16), opad = new Array(16);
  for (var i = 0; i < 16; i++)
  {
    ipad[i] = bkey[i] ^ 0x36363636;
    opad[i] = bkey[i] ^ 0x5C5C5C5C;
  }

  var hash = core_sha1(ipad.concat(str2binb(data)), 512 + data.length * 8);
  return core_sha1(opad.concat(hash), 512 + 160);
}

/*
 * Add integers, wrapping at 2^32. This uses 16-bit operations internally
 * to work around bugs in some JS interpreters.
 */
function safe_add(x, y)
{
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
  return (msw << 16) | (lsw & 0xFFFF);
}

/*
 * Bitwise rotate a 32-bit number to the left.
 */
function rol(num, cnt)
{
  return (num << cnt) | (num >>> (32 - cnt));
}

/*
 * Convert an 8-bit or 16-bit string to an array of big-endian words
 * In 8-bit function, characters >255 have their hi-byte silently ignored.
 */
function str2binb(str)
{
  var bin = [];
  var mask = 255;
  for (var i = 0; i < str.length * 8; i += 8)
  {
    bin[i>>5] |= (str.charCodeAt(i / 8) & mask) << (24 - i%32);
  }
  return bin;
}

/*
 * Convert an array of big-endian words to a string
 */
function binb2str(bin)
{
  var str = "";
  var mask = 255;
  for (var i = 0; i < bin.length * 32; i += 8)
  {
    str += String.fromCharCode((bin[i>>5] >>> (24 - i%32)) & mask);
  }
  return str;
}

/*
 * Convert an array of big-endian words to a base-64 string
 */
function binb2b64(binarray)
{
  var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
  var str = "";
  var triplet, j;
  for (var i = 0; i < binarray.length * 4; i += 3)
  {
    triplet = (((binarray[i   >> 2] >> 8 * (3 -  i   %4)) & 0xFF) << 16) |
              (((binarray[i+1 >> 2] >> 8 * (3 - (i+1)%4)) & 0xFF) << 8 ) |
               ((binarray[i+2 >> 2] >> 8 * (3 - (i+2)%4)) & 0xFF);
    for (j = 0; j < 4; j++)
    {
      if (i * 8 + j * 6 > binarray.length * 32) { str += "="; }
      else { str += tab.charAt((triplet >> 6*(3-j)) & 0x3F); }
    }
  }
  return str;
}

/*
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
 * Digest Algorithm, as defined in RFC 1321.
 * Version 2.1 Copyright (C) Paul Johnston 1999 - 2002.
 * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
 * Distributed under the BSD License
 * See http://pajhome.org.uk/crypt/md5 for more info.
 */

/*
 * Everything that isn't used by Strophe has been stripped here!
 */

var MD5 = (function () {
    /*
     * Add integers, wrapping at 2^32. This uses 16-bit operations internally
     * to work around bugs in some JS interpreters.
     */
    var safe_add = function (x, y) {
        var lsw = (x & 0xFFFF) + (y & 0xFFFF);
        var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
    };

    /*
     * Bitwise rotate a 32-bit number to the left.
     */
    var bit_rol = function (num, cnt) {
        return (num << cnt) | (num >>> (32 - cnt));
    };

    /*
     * Convert a string to an array of little-endian words
     */
    var str2binl = function (str) {
        var bin = [];
        for(var i = 0; i < str.length * 8; i += 8)
        {
            bin[i>>5] |= (str.charCodeAt(i / 8) & 255) << (i%32);
        }
        return bin;
    };

    /*
     * Convert an array of little-endian words to a string
     */
    var binl2str = function (bin) {
        var str = "";
        for(var i = 0; i < bin.length * 32; i += 8)
        {
            str += String.fromCharCode((bin[i>>5] >>> (i % 32)) & 255);
        }
        return str;
    };

    /*
     * Convert an array of little-endian words to a hex string.
     */
    var binl2hex = function (binarray) {
        var hex_tab = "0123456789abcdef";
        var str = "";
        for(var i = 0; i < binarray.length * 4; i++)
        {
            str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) +
                hex_tab.charAt((binarray[i>>2] >> ((i%4)*8  )) & 0xF);
        }
        return str;
    };

    /*
     * These functions implement the four basic operations the algorithm uses.
     */
    var md5_cmn = function (q, a, b, x, s, t) {
        return safe_add(bit_rol(safe_add(safe_add(a, q),safe_add(x, t)), s),b);
    };

    var md5_ff = function (a, b, c, d, x, s, t) {
        return md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);
    };

    var md5_gg = function (a, b, c, d, x, s, t) {
        return md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);
    };

    var md5_hh = function (a, b, c, d, x, s, t) {
        return md5_cmn(b ^ c ^ d, a, b, x, s, t);
    };

    var md5_ii = function (a, b, c, d, x, s, t) {
        return md5_cmn(c ^ (b | (~d)), a, b, x, s, t);
    };

    /*
     * Calculate the MD5 of an array of little-endian words, and a bit length
     */
    var core_md5 = function (x, len) {
        /* append padding */
        x[len >> 5] |= 0x80 << ((len) % 32);
        x[(((len + 64) >>> 9) << 4) + 14] = len;

        var a =  1732584193;
        var b = -271733879;
        var c = -1732584194;
        var d =  271733878;

        var olda, oldb, oldc, oldd;
        for (var i = 0; i < x.length; i += 16)
        {
            olda = a;
            oldb = b;
            oldc = c;
            oldd = d;

            a = md5_ff(a, b, c, d, x[i+ 0], 7 , -680876936);
            d = md5_ff(d, a, b, c, x[i+ 1], 12, -389564586);
            c = md5_ff(c, d, a, b, x[i+ 2], 17,  606105819);
            b = md5_ff(b, c, d, a, x[i+ 3], 22, -1044525330);
            a = md5_ff(a, b, c, d, x[i+ 4], 7 , -176418897);
            d = md5_ff(d, a, b, c, x[i+ 5], 12,  1200080426);
            c = md5_ff(c, d, a, b, x[i+ 6], 17, -1473231341);
            b = md5_ff(b, c, d, a, x[i+ 7], 22, -45705983);
            a = md5_ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
            d = md5_ff(d, a, b, c, x[i+ 9], 12, -1958414417);
            c = md5_ff(c, d, a, b, x[i+10], 17, -42063);
            b = md5_ff(b, c, d, a, x[i+11], 22, -1990404162);
            a = md5_ff(a, b, c, d, x[i+12], 7 ,  1804603682);
            d = md5_ff(d, a, b, c, x[i+13], 12, -40341101);
            c = md5_ff(c, d, a, b, x[i+14], 17, -1502002290);
            b = md5_ff(b, c, d, a, x[i+15], 22,  1236535329);

            a = md5_gg(a, b, c, d, x[i+ 1], 5 , -165796510);
            d = md5_gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
            c = md5_gg(c, d, a, b, x[i+11], 14,  643717713);
            b = md5_gg(b, c, d, a, x[i+ 0], 20, -373897302);
            a = md5_gg(a, b, c, d, x[i+ 5], 5 , -701558691);
            d = md5_gg(d, a, b, c, x[i+10], 9 ,  38016083);
            c = md5_gg(c, d, a, b, x[i+15], 14, -660478335);
            b = md5_gg(b, c, d, a, x[i+ 4], 20, -405537848);
            a = md5_gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
            d = md5_gg(d, a, b, c, x[i+14], 9 , -1019803690);
            c = md5_gg(c, d, a, b, x[i+ 3], 14, -187363961);
            b = md5_gg(b, c, d, a, x[i+ 8], 20,  1163531501);
            a = md5_gg(a, b, c, d, x[i+13], 5 , -1444681467);
            d = md5_gg(d, a, b, c, x[i+ 2], 9 , -51403784);
            c = md5_gg(c, d, a, b, x[i+ 7], 14,  1735328473);
            b = md5_gg(b, c, d, a, x[i+12], 20, -1926607734);

            a = md5_hh(a, b, c, d, x[i+ 5], 4 , -378558);
            d = md5_hh(d, a, b, c, x[i+ 8], 11, -2022574463);
            c = md5_hh(c, d, a, b, x[i+11], 16,  1839030562);
            b = md5_hh(b, c, d, a, x[i+14], 23, -35309556);
            a = md5_hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
            d = md5_hh(d, a, b, c, x[i+ 4], 11,  1272893353);
            c = md5_hh(c, d, a, b, x[i+ 7], 16, -155497632);
            b = md5_hh(b, c, d, a, x[i+10], 23, -1094730640);
            a = md5_hh(a, b, c, d, x[i+13], 4 ,  681279174);
            d = md5_hh(d, a, b, c, x[i+ 0], 11, -358537222);
            c = md5_hh(c, d, a, b, x[i+ 3], 16, -722521979);
            b = md5_hh(b, c, d, a, x[i+ 6], 23,  76029189);
            a = md5_hh(a, b, c, d, x[i+ 9], 4 , -640364487);
            d = md5_hh(d, a, b, c, x[i+12], 11, -421815835);
            c = md5_hh(c, d, a, b, x[i+15], 16,  530742520);
            b = md5_hh(b, c, d, a, x[i+ 2], 23, -995338651);

            a = md5_ii(a, b, c, d, x[i+ 0], 6 , -198630844);
            d = md5_ii(d, a, b, c, x[i+ 7], 10,  1126891415);
            c = md5_ii(c, d, a, b, x[i+14], 15, -1416354905);
            b = md5_ii(b, c, d, a, x[i+ 5], 21, -57434055);
            a = md5_ii(a, b, c, d, x[i+12], 6 ,  1700485571);
            d = md5_ii(d, a, b, c, x[i+ 3], 10, -1894986606);
            c = md5_ii(c, d, a, b, x[i+10], 15, -1051523);
            b = md5_ii(b, c, d, a, x[i+ 1], 21, -2054922799);
            a = md5_ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
            d = md5_ii(d, a, b, c, x[i+15], 10, -30611744);
            c = md5_ii(c, d, a, b, x[i+ 6], 15, -1560198380);
            b = md5_ii(b, c, d, a, x[i+13], 21,  1309151649);
            a = md5_ii(a, b, c, d, x[i+ 4], 6 , -145523070);
            d = md5_ii(d, a, b, c, x[i+11], 10, -1120210379);
            c = md5_ii(c, d, a, b, x[i+ 2], 15,  718787259);
            b = md5_ii(b, c, d, a, x[i+ 9], 21, -343485551);

            a = safe_add(a, olda);
            b = safe_add(b, oldb);
            c = safe_add(c, oldc);
            d = safe_add(d, oldd);
        }
        return [a, b, c, d];
    };


    var obj = {
        /*
         * These are the functions you'll usually want to call.
         * They take string arguments and return either hex or base-64 encoded
         * strings.
         */
        hexdigest: function (s) {
            return binl2hex(core_md5(str2binl(s), s.length * 8));
        },

        hash: function (s) {
            return binl2str(core_md5(str2binl(s), s.length * 8));
        }
    };

    return obj;
})();

/*
    This program is distributed under the terms of the MIT license.
    Please see the LICENSE file for details.

    Copyright 2006-2008, OGG, LLC
*/

/* jshint undef: true, unused: true:, noarg: true, latedef: true */

/** File: polyfills.js
 *  A JavaScript library for XMPP BOSH/XMPP over Websocket.
 *
 *  This file contains some polyfills used by strophe.js
 */

/** PrivateFunction: Function.prototype.bind
 *  Bind a function to an instance.
 *
 *  This Function object extension method creates a bound method similar
 *  to those in Python.  This means that the 'this' object will point
 *  to the instance you want.  See
 *  <a href='https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Function/bind'>MDC's bind() documentation</a> and
 *  <a href='http://benjamin.smedbergs.us/blog/2007-01-03/bound-functions-and-function-imports-in-javascript/'>Bound Functions and Function Imports in JavaScript</a>
 *  for a complete explanation.
 *
 *  This extension already exists in some browsers (namely, Firefox 3), but
 *  we provide it to support those that don't.
 *
 *  Parameters:
 *    (Object) obj - The object that will become 'this' in the bound function.
 *    (Object) argN - An option argument that will be prepended to the
 *      arguments given for the function call
 *
 *  Returns:
 *    The bound function.
 */
if (!Function.prototype.bind) {
    Function.prototype.bind = function (obj /*, arg1, arg2, ... */)
    {
        var func = this;
        var _slice = Array.prototype.slice;
        var _concat = Array.prototype.concat;
        var _args = _slice.call(arguments, 1);

        return function () {
            return func.apply(obj ? obj : this,
                              _concat.call(_args,
                                           _slice.call(arguments, 0)));
        };
    };
}

/** PrivateFunction: Array.isArray
 *  This is a polyfill for the ES5 Array.isArray method.
 */
if (!Array.isArray) {
    Array.isArray = function(arg) {
        return Object.prototype.toString.call(arg) === '[object Array]';
    };
}

/** PrivateFunction: Array.prototype.indexOf
 *  Return the index of an object in an array.
 *
 *  This function is not supplied by some JavaScript implementations, so
 *  we provide it if it is missing.  This code is from:
 *  http://developer.mozilla.org/En/Core_JavaScript_1.5_Reference:Objects:Array:indexOf
 *
 *  Parameters:
 *    (Object) elt - The object to look for.
 *    (Integer) from - The index from which to start looking. (optional).
 *
 *  Returns:
 *    The index of elt in the array or -1 if not found.
 */
if (!Array.prototype.indexOf)
    {
        Array.prototype.indexOf = function(elt /*, from*/)
        {
            var len = this.length;

            var from = Number(arguments[1]) || 0;
            from = (from < 0) ? Math.ceil(from) : Math.floor(from);
            if (from < 0) {
                from += len;
            }

            for (; from < len; from++) {
                if (from in this && this[from] === elt) {
                    return from;
                }
            }

            return -1;
        };
    }

/*
    This program is distributed under the terms of the MIT license.
    Please see the LICENSE file for details.

    Copyright 2006-2008, OGG, LLC
*/

/* jshint undef: true, unused: true:, noarg: true, latedef: true */
/*global document, window, setTimeout, clearTimeout, console,
    ActiveXObject, Base64, MD5, DOMParser */
// from sha1.js
/*global core_hmac_sha1, binb2str, str_hmac_sha1, str_sha1, b64_hmac_sha1*/

/** File: strophe.js
 *  A JavaScript library for XMPP BOSH/XMPP over Websocket.
 *
 *  This is the JavaScript version of the Strophe library.  Since JavaScript
 *  had no facilities for persistent TCP connections, this library uses
 *  Bidirectional-streams Over Synchronous HTTP (BOSH) to emulate
 *  a persistent, stateful, two-way connection to an XMPP server.  More
 *  information on BOSH can be found in XEP 124.
 *
 *  This version of Strophe also works with WebSockets.
 *  For more information on XMPP-over WebSocket see this RFC draft:
 *  http://tools.ietf.org/html/draft-ietf-xmpp-websocket-00
 */

/* All of the Strophe globals are defined in this special function below so
 * that references to the globals become closures.  This will ensure that
 * on page reload, these references will still be available to callbacks
 * that are still executing.
 */

var Strophe;

/** Function: $build
 *  Create a Strophe.Builder.
 *  This is an alias for 'new Strophe.Builder(name, attrs)'.
 *
 *  Parameters:
 *    (String) name - The root element name.
 *    (Object) attrs - The attributes for the root element in object notation.
 *
 *  Returns:
 *    A new Strophe.Builder object.
 */
function $build(name, attrs) { return new Strophe.Builder(name, attrs); }
/** Function: $msg
 *  Create a Strophe.Builder with a <message/> element as the root.
 *
 *  Parmaeters:
 *    (Object) attrs - The <message/> element attributes in object notation.
 *
 *  Returns:
 *    A new Strophe.Builder object.
 */
/* jshint ignore:start */
function $msg(attrs) { return new Strophe.Builder("message", attrs); }
/* jshint ignore:end */
/** Function: $iq
 *  Create a Strophe.Builder with an <iq/> element as the root.
 *
 *  Parameters:
 *    (Object) attrs - The <iq/> element attributes in object notation.
 *
 *  Returns:
 *    A new Strophe.Builder object.
 */
function $iq(attrs) { return new Strophe.Builder("iq", attrs); }
/** Function: $pres
 *  Create a Strophe.Builder with a <presence/> element as the root.
 *
 *  Parameters:
 *    (Object) attrs - The <presence/> element attributes in object notation.
 *
 *  Returns:
 *    A new Strophe.Builder object.
 */
function $pres(attrs) { return new Strophe.Builder("presence", attrs); }

/** Class: Strophe
 *  An object container for all Strophe library functions.
 *
 *  This class is just a container for all the objects and constants
 *  used in the library.  It is not meant to be instantiated, but to
 *  provide a namespace for library objects, constants, and functions.
 */
Strophe = {
    /** Constant: VERSION
     *  The version of the Strophe library. Unreleased builds will have
     *  a version of head-HASH where HASH is a partial revision.
     */
    VERSION: "1.1.4dev1",

    /** Constants: XMPP Namespace Constants
     *  Common namespace constants from the XMPP RFCs and XEPs.
     *
     *  NS.HTTPBIND - HTTP BIND namespace from XEP 124.
     *  NS.BOSH - BOSH namespace from XEP 206.
     *  NS.CLIENT - Main XMPP client namespace.
     *  NS.AUTH - Legacy authentication namespace.
     *  NS.ROSTER - Roster operations namespace.
     *  NS.PROFILE - Profile namespace.
     *  NS.DISCO_INFO - Service discovery info namespace from XEP 30.
     *  NS.DISCO_ITEMS - Service discovery items namespace from XEP 30.
     *  NS.MUC - Multi-User Chat namespace from XEP 45.
     *  NS.SASL - XMPP SASL namespace from RFC 3920.
     *  NS.STREAM - XMPP Streams namespace from RFC 3920.
     *  NS.BIND - XMPP Binding namespace from RFC 3920.
     *  NS.SESSION - XMPP Session namespace from RFC 3920.
     *  NS.XHTML_IM - XHTML-IM namespace from XEP 71.
     *  NS.XHTML - XHTML body namespace from XEP 71.
     */
    NS: {
        HTTPBIND: "http://jabber.org/protocol/httpbind",
        BOSH: "urn:xmpp:xbosh",
        CLIENT: "jabber:client",
        AUTH: "jabber:iq:auth",
        ROSTER: "jabber:iq:roster",
        PROFILE: "jabber:iq:profile",
        DISCO_INFO: "http://jabber.org/protocol/disco#info",
        DISCO_ITEMS: "http://jabber.org/protocol/disco#items",
        MUC: "http://jabber.org/protocol/muc",
        SASL: "urn:ietf:params:xml:ns:xmpp-sasl",
        STREAM: "http://etherx.jabber.org/streams",
        FRAMING: "urn:ietf:params:xml:ns:xmpp-framing",
        BIND: "urn:ietf:params:xml:ns:xmpp-bind",
        SESSION: "urn:ietf:params:xml:ns:xmpp-session",
        VERSION: "jabber:iq:version",
        STANZAS: "urn:ietf:params:xml:ns:xmpp-stanzas",
        XHTML_IM: "http://jabber.org/protocol/xhtml-im",
        XHTML: "http://www.w3.org/1999/xhtml"
    },


    /** Constants: XHTML_IM Namespace
     *  contains allowed tags, tag attributes, and css properties.
     *  Used in the createHtml function to filter incoming html into the allowed XHTML-IM subset.
     *  See http://xmpp.org/extensions/xep-0071.html#profile-summary for the list of recommended
     *  allowed tags and their attributes.
     */
    XHTML: {
                tags: ['a','blockquote','br','cite','em','img','li','ol','p','span','strong','ul','body'],
                attributes: {
                        'a':          ['href'],
                        'blockquote': ['style'],
                        'br':         [],
                        'cite':       ['style'],
                        'em':         [],
                        'img':        ['src', 'alt', 'style', 'height', 'width'],
                        'li':         ['style'],
                        'ol':         ['style'],
                        'p':          ['style'],
                        'span':       ['style'],
                        'strong':     [],
                        'ul':         ['style'],
                        'body':       []
                },
                css: ['background-color','color','font-family','font-size','font-style','font-weight','margin-left','margin-right','text-align','text-decoration'],
                validTag: function(tag)
                {
                        for(var i = 0; i < Strophe.XHTML.tags.length; i++) {
                                if(tag == Strophe.XHTML.tags[i]) {
                                        return true;
                                }
                        }
                        return false;
                },
                validAttribute: function(tag, attribute)
                {
                        if(typeof Strophe.XHTML.attributes[tag] !== 'undefined' && Strophe.XHTML.attributes[tag].length > 0) {
                                for(var i = 0; i < Strophe.XHTML.attributes[tag].length; i++) {
                                        if(attribute == Strophe.XHTML.attributes[tag][i]) {
                                                return true;
                                        }
                                }
                        }
                        return false;
                },
                validCSS: function(style)
                {
                        for(var i = 0; i < Strophe.XHTML.css.length; i++) {
                                if(style == Strophe.XHTML.css[i]) {
                                        return true;
                                }
                        }
                        return false;
                }
    },

    /** Constants: Connection Status Constants
     *  Connection status constants for use by the connection handler
     *  callback.
     *
     *  Status.ERROR - An error has occurred
     *  Status.CONNECTING - The connection is currently being made
     *  Status.CONNFAIL - The connection attempt failed
     *  Status.AUTHENTICATING - The connection is authenticating
     *  Status.AUTHFAIL - The authentication attempt failed
     *  Status.CONNECTED - The connection has succeeded
     *  Status.DISCONNECTED - The connection has been terminated
     *  Status.DISCONNECTING - The connection is currently being terminated
     *  Status.ATTACHED - The connection has been attached
     */
    Status: {
        ERROR: 0,
        CONNECTING: 1,
        CONNFAIL: 2,
        AUTHENTICATING: 3,
        AUTHFAIL: 4,
        CONNECTED: 5,
        DISCONNECTED: 6,
        DISCONNECTING: 7,
        ATTACHED: 8,
        REDIRECT: 9
    },

    /** Constants: Log Level Constants
     *  Logging level indicators.
     *
     *  LogLevel.DEBUG - Debug output
     *  LogLevel.INFO - Informational output
     *  LogLevel.WARN - Warnings
     *  LogLevel.ERROR - Errors
     *  LogLevel.FATAL - Fatal errors
     */
    LogLevel: {
        DEBUG: 0,
        INFO: 1,
        WARN: 2,
        ERROR: 3,
        FATAL: 4
    },

    /** PrivateConstants: DOM Element Type Constants
     *  DOM element types.
     *
     *  ElementType.NORMAL - Normal element.
     *  ElementType.TEXT - Text data element.
     *  ElementType.FRAGMENT - XHTML fragment element.
     */
    ElementType: {
        NORMAL: 1,
        TEXT: 3,
        CDATA: 4,
        FRAGMENT: 11
    },

    /** PrivateConstants: Timeout Values
     *  Timeout values for error states.  These values are in seconds.
     *  These should not be changed unless you know exactly what you are
     *  doing.
     *
     *  TIMEOUT - Timeout multiplier. A waiting request will be considered
     *      failed after Math.floor(TIMEOUT * wait) seconds have elapsed.
     *      This defaults to 1.1, and with default wait, 66 seconds.
     *  SECONDARY_TIMEOUT - Secondary timeout multiplier. In cases where
     *      Strophe can detect early failure, it will consider the request
     *      failed if it doesn't return after
     *      Math.floor(SECONDARY_TIMEOUT * wait) seconds have elapsed.
     *      This defaults to 0.1, and with default wait, 6 seconds.
     */
    TIMEOUT: 1.1,
    SECONDARY_TIMEOUT: 0.1,

    /** Function: addNamespace
     *  This function is used to extend the current namespaces in
     *  Strophe.NS.  It takes a key and a value with the key being the
     *  name of the new namespace, with its actual value.
     *  For example:
     *  Strophe.addNamespace('PUBSUB', "http://jabber.org/protocol/pubsub");
     *
     *  Parameters:
     *    (String) name - The name under which the namespace will be
     *      referenced under Strophe.NS
     *    (String) value - The actual namespace.
     */
    addNamespace: function (name, value)
    {
      Strophe.NS[name] = value;
    },

    /** Function: forEachChild
     *  Map a function over some or all child elements of a given element.
     *
     *  This is a small convenience function for mapping a function over
     *  some or all of the children of an element.  If elemName is null, all
     *  children will be passed to the function, otherwise only children
     *  whose tag names match elemName will be passed.
     *
     *  Parameters:
     *    (XMLElement) elem - The element to operate on.
     *    (String) elemName - The child element tag name filter.
     *    (Function) func - The function to apply to each child.  This
     *      function should take a single argument, a DOM element.
     */
    forEachChild: function (elem, elemName, func)
    {
        var i, childNode;

        for (i = 0; i < elem.childNodes.length; i++) {
            childNode = elem.childNodes[i];
            if (childNode.nodeType == Strophe.ElementType.NORMAL &&
                (!elemName || this.isTagEqual(childNode, elemName))) {
                func(childNode);
            }
        }
    },

    /** Function: isTagEqual
     *  Compare an element's tag name with a string.
     *
     *  This function is case sensitive.
     *
     *  Parameters:
     *    (XMLElement) el - A DOM element.
     *    (String) name - The element name.
     *
     *  Returns:
     *    true if the element's tag name matches _el_, and false
     *    otherwise.
     */
    isTagEqual: function (el, name)
    {
        return el.tagName == name;
    },

    /** PrivateVariable: _xmlGenerator
     *  _Private_ variable that caches a DOM document to
     *  generate elements.
     */
    _xmlGenerator: null,

    /** PrivateFunction: _makeGenerator
     *  _Private_ function that creates a dummy XML DOM document to serve as
     *  an element and text node generator.
     */
    _makeGenerator: function () {
        var doc;

        // IE9 does implement createDocument(); however, using it will cause the browser to leak memory on page unload.
        // Here, we test for presence of createDocument() plus IE's proprietary documentMode attribute, which would be
                // less than 10 in the case of IE9 and below.
        if (document.implementation.createDocument === undefined ||
                        document.implementation.createDocument && document.documentMode && document.documentMode < 10) {
            doc = this._getIEXmlDom();
            doc.appendChild(doc.createElement('strophe'));
        } else {
            doc = document.implementation
                .createDocument('jabber:client', 'strophe', null);
        }

        return doc;
    },

    /** Function: xmlGenerator
     *  Get the DOM document to generate elements.
     *
     *  Returns:
     *    The currently used DOM document.
     */
    xmlGenerator: function () {
        if (!Strophe._xmlGenerator) {
            Strophe._xmlGenerator = Strophe._makeGenerator();
        }
        return Strophe._xmlGenerator;
    },

    /** PrivateFunction: _getIEXmlDom
     *  Gets IE xml doc object
     *
     *  Returns:
     *    A Microsoft XML DOM Object
     *  See Also:
     *    http://msdn.microsoft.com/en-us/library/ms757837%28VS.85%29.aspx
     */
    _getIEXmlDom : function() {
        var doc = null;
        var docStrings = [
            "Msxml2.DOMDocument.6.0",
            "Msxml2.DOMDocument.5.0",
            "Msxml2.DOMDocument.4.0",
            "MSXML2.DOMDocument.3.0",
            "MSXML2.DOMDocument",
            "MSXML.DOMDocument",
            "Microsoft.XMLDOM"
        ];

        for (var d = 0; d < docStrings.length; d++) {
            if (doc === null) {
                try {
                    doc = new ActiveXObject(docStrings[d]);
                } catch (e) {
                    doc = null;
                }
            } else {
                break;
            }
        }

        return doc;
    },

    /** Function: xmlElement
     *  Create an XML DOM element.
     *
     *  This function creates an XML DOM element correctly across all
     *  implementations. Note that these are not HTML DOM elements, which
     *  aren't appropriate for XMPP stanzas.
     *
     *  Parameters:
     *    (String) name - The name for the element.
     *    (Array|Object) attrs - An optional array or object containing
     *      key/value pairs to use as element attributes. The object should
     *      be in the format {'key': 'value'} or {key: 'value'}. The array
     *      should have the format [['key1', 'value1'], ['key2', 'value2']].
     *    (String) text - The text child data for the element.
     *
     *  Returns:
     *    A new XML DOM element.
     */
    xmlElement: function (name)
    {
        if (!name) { return null; }

        var node = Strophe.xmlGenerator().createElement(name);

        // FIXME: this should throw errors if args are the wrong type or
        // there are more than two optional args
        var a, i, k;
        for (a = 1; a < arguments.length; a++) {
            if (!arguments[a]) { continue; }
            if (typeof(arguments[a]) == "string" ||
                typeof(arguments[a]) == "number") {
                node.appendChild(Strophe.xmlTextNode(arguments[a]));
            } else if (typeof(arguments[a]) == "object" &&
                       typeof(arguments[a].sort) == "function") {
                for (i = 0; i < arguments[a].length; i++) {
                    if (typeof(arguments[a][i]) == "object" &&
                        typeof(arguments[a][i].sort) == "function") {
                        node.setAttribute(arguments[a][i][0],
                                          arguments[a][i][1]);
                    }
                }
            } else if (typeof(arguments[a]) == "object") {
                for (k in arguments[a]) {
                    if (arguments[a].hasOwnProperty(k)) {
                        node.setAttribute(k, arguments[a][k]);
                    }
                }
            }
        }

        return node;
    },

    /*  Function: xmlescape
     *  Excapes invalid xml characters.
     *
     *  Parameters:
     *     (String) text - text to escape.
     *
     *  Returns:
     *      Escaped text.
     */
    xmlescape: function(text)
    {
        text = text.replace(/\&/g, "&amp;");
        text = text.replace(/</g,  "&lt;");
        text = text.replace(/>/g,  "&gt;");
        text = text.replace(/'/g,  "&apos;");
        text = text.replace(/"/g,  "&quot;");
        return text;
    },

    /*  Function: xmlunescape
    *  Unexcapes invalid xml characters.
    *
    *  Parameters:
    *     (String) text - text to unescape.
    *
    *  Returns:
    *      Unescaped text.
    */
    xmlunescape: function(text)
    {
        text = text.replace(/\&amp;/g, "&");
        text = text.replace(/&lt;/g,  "<");
        text = text.replace(/&gt;/g,  ">");
        text = text.replace(/&apos;/g,  "'");
        text = text.replace(/&quot;/g,  "\"");
        return text;
    },

    /** Function: xmlTextNode
     *  Creates an XML DOM text node.
     *
     *  Provides a cross implementation version of document.createTextNode.
     *
     *  Parameters:
     *    (String) text - The content of the text node.
     *
     *  Returns:
     *    A new XML DOM text node.
     */
    xmlTextNode: function (text)
    {
        return Strophe.xmlGenerator().createTextNode(text);
    },

    /** Function: xmlHtmlNode
     *  Creates an XML DOM html node.
     *
     *  Parameters:
     *    (String) html - The content of the html node.
     *
     *  Returns:
     *    A new XML DOM text node.
     */
    xmlHtmlNode: function (html)
    {
        var node;
        //ensure text is escaped
        if (window.DOMParser) {
            var parser = new DOMParser();
            node = parser.parseFromString(html, "text/xml");
        } else {
            node = new ActiveXObject("Microsoft.XMLDOM");
            node.async="false";
            node.loadXML(html);
        }
        return node;
    },

    /** Function: getText
     *  Get the concatenation of all text children of an element.
     *
     *  Parameters:
     *    (XMLElement) elem - A DOM element.
     *
     *  Returns:
     *    A String with the concatenated text of all text element children.
     */
    getText: function (elem)
    {
        if (!elem) { return null; }

        var str = "";
        if (elem.childNodes.length === 0 && elem.nodeType ==
            Strophe.ElementType.TEXT) {
            str += elem.nodeValue;
        }

        for (var i = 0; i < elem.childNodes.length; i++) {
            if (elem.childNodes[i].nodeType == Strophe.ElementType.TEXT) {
                str += elem.childNodes[i].nodeValue;
            }
        }

        return Strophe.xmlescape(str);
    },

    /** Function: copyElement
     *  Copy an XML DOM element.
     *
     *  This function copies a DOM element and all its descendants and returns
     *  the new copy.
     *
     *  Parameters:
     *    (XMLElement) elem - A DOM element.
     *
     *  Returns:
     *    A new, copied DOM element tree.
     */
    copyElement: function (elem)
    {
        var i, el;
        if (elem.nodeType == Strophe.ElementType.NORMAL) {
            el = Strophe.xmlElement(elem.tagName);

            for (i = 0; i < elem.attributes.length; i++) {
                el.setAttribute(elem.attributes[i].nodeName,
                                elem.attributes[i].value);
            }

            for (i = 0; i < elem.childNodes.length; i++) {
                el.appendChild(Strophe.copyElement(elem.childNodes[i]));
            }
        } else if (elem.nodeType == Strophe.ElementType.TEXT) {
            el = Strophe.xmlGenerator().createTextNode(elem.nodeValue);
        }

        return el;
    },


    /** Function: createHtml
     *  Copy an HTML DOM element into an XML DOM.
     *
     *  This function copies a DOM element and all its descendants and returns
     *  the new copy.
     *
     *  Parameters:
     *    (HTMLElement) elem - A DOM element.
     *
     *  Returns:
     *    A new, copied DOM element tree.
     */
    createHtml: function (elem)
    {
        var i, el, j, tag, attribute, value, css, cssAttrs, attr, cssName, cssValue;
        if (elem.nodeType == Strophe.ElementType.NORMAL) {
            tag = elem.nodeName;
            if(Strophe.XHTML.validTag(tag)) {
                try {
                    el = Strophe.xmlElement(tag);
                    for(i = 0; i < Strophe.XHTML.attributes[tag].length; i++) {
                        attribute = Strophe.XHTML.attributes[tag][i];
                        value = elem.getAttribute(attribute);
                        if(typeof value == 'undefined' || value === null || value === '' || value === false || value === 0) {
                            continue;
                        }
                        if(attribute == 'style' && typeof value == 'object') {
                            if(typeof value.cssText != 'undefined') {
                                value = value.cssText; // we're dealing with IE, need to get CSS out
                            }
                        }
                        // filter out invalid css styles
                        if(attribute == 'style') {
                            css = [];
                            cssAttrs = value.split(';');
                            for(j = 0; j < cssAttrs.length; j++) {
                                attr = cssAttrs[j].split(':');
                                cssName = attr[0].replace(/^\s*/, "").replace(/\s*$/, "").toLowerCase();
                                if(Strophe.XHTML.validCSS(cssName)) {
                                    cssValue = attr[1].replace(/^\s*/, "").replace(/\s*$/, "");
                                    css.push(cssName + ': ' + cssValue);
                                }
                            }
                            if(css.length > 0) {
                                value = css.join('; ');
                                el.setAttribute(attribute, value);
                            }
                        } else {
                            el.setAttribute(attribute, value);
                        }
                    }

                    for (i = 0; i < elem.childNodes.length; i++) {
                        el.appendChild(Strophe.createHtml(elem.childNodes[i]));
                    }
                } catch(e) { // invalid elements
                  el = Strophe.xmlTextNode('');
                }
            } else {
                el = Strophe.xmlGenerator().createDocumentFragment();
                for (i = 0; i < elem.childNodes.length; i++) {
                    el.appendChild(Strophe.createHtml(elem.childNodes[i]));
                }
            }
        } else if (elem.nodeType == Strophe.ElementType.FRAGMENT) {
            el = Strophe.xmlGenerator().createDocumentFragment();
            for (i = 0; i < elem.childNodes.length; i++) {
                el.appendChild(Strophe.createHtml(elem.childNodes[i]));
            }
        } else if (elem.nodeType == Strophe.ElementType.TEXT) {
            el = Strophe.xmlTextNode(elem.nodeValue);
        }

        return el;
    },

    /** Function: escapeNode
     *  Escape the node part (also called local part) of a JID.
     *
     *  Parameters:
     *    (String) node - A node (or local part).
     *
     *  Returns:
     *    An escaped node (or local part).
     */
    escapeNode: function (node)
    {
        if (typeof node !== "string") { return node; }
        return node.replace(/^\s+|\s+$/g, '')
            .replace(/\\/g,  "\\5c")
            .replace(/ /g,   "\\20")
            .replace(/\"/g,  "\\22")
            .replace(/\&/g,  "\\26")
            .replace(/\'/g,  "\\27")
            .replace(/\//g,  "\\2f")
            .replace(/:/g,   "\\3a")
            .replace(/</g,   "\\3c")
            .replace(/>/g,   "\\3e")
            .replace(/@/g,   "\\40");
    },

    /** Function: unescapeNode
     *  Unescape a node part (also called local part) of a JID.
     *
     *  Parameters:
     *    (String) node - A node (or local part).
     *
     *  Returns:
     *    An unescaped node (or local part).
     */
    unescapeNode: function (node)
    {
        if (typeof node !== "string") { return node; }
        return node.replace(/\\20/g, " ")
            .replace(/\\22/g, '"')
            .replace(/\\26/g, "&")
            .replace(/\\27/g, "'")
            .replace(/\\2f/g, "/")
            .replace(/\\3a/g, ":")
            .replace(/\\3c/g, "<")
            .replace(/\\3e/g, ">")
            .replace(/\\40/g, "@")
            .replace(/\\5c/g, "\\");
    },

    /** Function: getNodeFromJid
     *  Get the node portion of a JID String.
     *
     *  Parameters:
     *    (String) jid - A JID.
     *
     *  Returns:
     *    A String containing the node.
     */
    getNodeFromJid: function (jid)
    {
        if (jid.indexOf("@") < 0) { return null; }
        return jid.split("@")[0];
    },

    /** Function: getDomainFromJid
     *  Get the domain portion of a JID String.
     *
     *  Parameters:
     *    (String) jid - A JID.
     *
     *  Returns:
     *    A String containing the domain.
     */
    getDomainFromJid: function (jid)
    {
        var bare = Strophe.getBareJidFromJid(jid);
        if (bare.indexOf("@") < 0) {
            return bare;
        } else {
            var parts = bare.split("@");
            parts.splice(0, 1);
            return parts.join('@');
        }
    },

    /** Function: getResourceFromJid
     *  Get the resource portion of a JID String.
     *
     *  Parameters:
     *    (String) jid - A JID.
     *
     *  Returns:
     *    A String containing the resource.
     */
    getResourceFromJid: function (jid)
    {
        var s = jid.split("/");
        if (s.length < 2) { return null; }
        s.splice(0, 1);
        return s.join('/');
    },

    /** Function: getBareJidFromJid
     *  Get the bare JID from a JID String.
     *
     *  Parameters:
     *    (String) jid - A JID.
     *
     *  Returns:
     *    A String containing the bare JID.
     */
    getBareJidFromJid: function (jid)
    {
        return jid ? jid.split("/")[0] : null;
    },

    /** Function: log
     *  User overrideable logging function.
     *
     *  This function is called whenever the Strophe library calls any
     *  of the logging functions.  The default implementation of this
     *  function does nothing.  If client code wishes to handle the logging
     *  messages, it should override this with
     *  > Strophe.log = function (level, msg) {
     *  >   (user code here)
     *  > };
     *
     *  Please note that data sent and received over the wire is logged
     *  via Strophe.Connection.rawInput() and Strophe.Connection.rawOutput().
     *
     *  The different levels and their meanings are
     *
     *    DEBUG - Messages useful for debugging purposes.
     *    INFO - Informational messages.  This is mostly information like
     *      'disconnect was called' or 'SASL auth succeeded'.
     *    WARN - Warnings about potential problems.  This is mostly used
     *      to report transient connection errors like request timeouts.
     *    ERROR - Some error occurred.
     *    FATAL - A non-recoverable fatal error occurred.
     *
     *  Parameters:
     *    (Integer) level - The log level of the log message.  This will
     *      be one of the values in Strophe.LogLevel.
     *    (String) msg - The log message.
     */
    /* jshint ignore:start */
    log: function (level, msg)
    {
        return;
    },
    /* jshint ignore:end */

    /** Function: debug
     *  Log a message at the Strophe.LogLevel.DEBUG level.
     *
     *  Parameters:
     *    (String) msg - The log message.
     */
    debug: function(msg)
    {
        this.log(this.LogLevel.DEBUG, msg);
    },

    /** Function: info
     *  Log a message at the Strophe.LogLevel.INFO level.
     *
     *  Parameters:
     *    (String) msg - The log message.
     */
    info: function (msg)
    {
        this.log(this.LogLevel.INFO, msg);
    },

    /** Function: warn
     *  Log a message at the Strophe.LogLevel.WARN level.
     *
     *  Parameters:
     *    (String) msg - The log message.
     */
    warn: function (msg)
    {
        this.log(this.LogLevel.WARN, msg);
    },

    /** Function: error
     *  Log a message at the Strophe.LogLevel.ERROR level.
     *
     *  Parameters:
     *    (String) msg - The log message.
     */
    error: function (msg)
    {
        this.log(this.LogLevel.ERROR, msg);
    },

    /** Function: fatal
     *  Log a message at the Strophe.LogLevel.FATAL level.
     *
     *  Parameters:
     *    (String) msg - The log message.
     */
    fatal: function (msg)
    {
        this.log(this.LogLevel.FATAL, msg);
    },

    /** Function: serialize
     *  Render a DOM element and all descendants to a String.
     *
     *  Parameters:
     *    (XMLElement) elem - A DOM element.
     *
     *  Returns:
     *    The serialized element tree as a String.
     */
    serialize: function (elem)
    {
        var result;

        if (!elem) { return null; }

        if (typeof(elem.tree) === "function") {
            elem = elem.tree();
        }

        var nodeName = elem.nodeName;
        var i, child;

        if (elem.getAttribute("_realname")) {
            nodeName = elem.getAttribute("_realname");
        }

        result = "<" + nodeName;
        for (i = 0; i < elem.attributes.length; i++) {
               if(elem.attributes[i].nodeName != "_realname") {
                 result += " " + elem.attributes[i].nodeName +
                "='" + elem.attributes[i].value
                    .replace(/&/g, "&amp;")
                       .replace(/\'/g, "&apos;")
                       .replace(/>/g, "&gt;")
                       .replace(/</g, "&lt;") + "'";
               }
        }

        if (elem.childNodes.length > 0) {
            result += ">";
            for (i = 0; i < elem.childNodes.length; i++) {
                child = elem.childNodes[i];
                switch( child.nodeType ){
                  case Strophe.ElementType.NORMAL:
                    // normal element, so recurse
                    result += Strophe.serialize(child);
                    break;
                  case Strophe.ElementType.TEXT:
                    // text element to escape values
                    result += Strophe.xmlescape(child.nodeValue);
                    break;
                  case Strophe.ElementType.CDATA:
                    // cdata section so don't escape values
                    result += "<![CDATA["+child.nodeValue+"]]>";
                }
            }
            result += "</" + nodeName + ">";
        } else {
            result += "/>";
        }

        return result;
    },

    /** PrivateVariable: _requestId
     *  _Private_ variable that keeps track of the request ids for
     *  connections.
     */
    _requestId: 0,

    /** PrivateVariable: Strophe.connectionPlugins
     *  _Private_ variable Used to store plugin names that need
     *  initialization on Strophe.Connection construction.
     */
    _connectionPlugins: {},

    /** Function: addConnectionPlugin
     *  Extends the Strophe.Connection object with the given plugin.
     *
     *  Parameters:
     *    (String) name - The name of the extension.
     *    (Object) ptype - The plugin's prototype.
     */
    addConnectionPlugin: function (name, ptype)
    {
        Strophe._connectionPlugins[name] = ptype;
    }
};

/** Class: Strophe.Builder
 *  XML DOM builder.
 *
 *  This object provides an interface similar to JQuery but for building
 *  DOM element easily and rapidly.  All the functions except for toString()
 *  and tree() return the object, so calls can be chained.  Here's an
 *  example using the $iq() builder helper.
 *  > $iq({to: 'you', from: 'me', type: 'get', id: '1'})
 *  >     .c('query', {xmlns: 'strophe:example'})
 *  >     .c('example')
 *  >     .toString()
 *  The above generates this XML fragment
 *  > <iq to='you' from='me' type='get' id='1'>
 *  >   <query xmlns='strophe:example'>
 *  >     <example/>
 *  >   </query>
 *  > </iq>
 *  The corresponding DOM manipulations to get a similar fragment would be
 *  a lot more tedious and probably involve several helper variables.
 *
 *  Since adding children makes new operations operate on the child, up()
 *  is provided to traverse up the tree.  To add two children, do
 *  > builder.c('child1', ...).up().c('child2', ...)
 *  The next operation on the Builder will be relative to the second child.
 */

/** Constructor: Strophe.Builder
 *  Create a Strophe.Builder object.
 *
 *  The attributes should be passed in object notation.  For example
 *  > var b = new Builder('message', {to: 'you', from: 'me'});
 *  or
 *  > var b = new Builder('messsage', {'xml:lang': 'en'});
 *
 *  Parameters:
 *    (String) name - The name of the root element.
 *    (Object) attrs - The attributes for the root element in object notation.
 *
 *  Returns:
 *    A new Strophe.Builder.
 */
Strophe.Builder = function (name, attrs)
{
    // Set correct namespace for jabber:client elements
    if (name == "presence" || name == "message" || name == "iq") {
        if (attrs && !attrs.xmlns) {
            attrs.xmlns = Strophe.NS.CLIENT;
        } else if (!attrs) {
            attrs = {xmlns: Strophe.NS.CLIENT};
        }
    }

    // Holds the tree being built.
    this.nodeTree = Strophe.xmlElement(name, attrs);

    // Points to the current operation node.
    this.node = this.nodeTree;
};

Strophe.Builder.prototype = {
    /** Function: tree
     *  Return the DOM tree.
     *
     *  This function returns the current DOM tree as an element object.  This
     *  is suitable for passing to functions like Strophe.Connection.send().
     *
     *  Returns:
     *    The DOM tree as a element object.
     */
    tree: function ()
    {
        return this.nodeTree;
    },

    /** Function: toString
     *  Serialize the DOM tree to a String.
     *
     *  This function returns a string serialization of the current DOM
     *  tree.  It is often used internally to pass data to a
     *  Strophe.Request object.
     *
     *  Returns:
     *    The serialized DOM tree in a String.
     */
    toString: function ()
    {
        return Strophe.serialize(this.nodeTree);
    },

    /** Function: up
     *  Make the current parent element the new current element.
     *
     *  This function is often used after c() to traverse back up the tree.
     *  For example, to add two children to the same element
     *  > builder.c('child1', {}).up().c('child2', {});
     *
     *  Returns:
     *    The Stophe.Builder object.
     */
    up: function ()
    {
        this.node = this.node.parentNode;
        return this;
    },

    /** Function: attrs
     *  Add or modify attributes of the current element.
     *
     *  The attributes should be passed in object notation.  This function
     *  does not move the current element pointer.
     *
     *  Parameters:
     *    (Object) moreattrs - The attributes to add/modify in object notation.
     *
     *  Returns:
     *    The Strophe.Builder object.
     */
    attrs: function (moreattrs)
    {
        for (var k in moreattrs) {
            if (moreattrs.hasOwnProperty(k)) {
                this.node.setAttribute(k, moreattrs[k]);
            }
        }
        return this;
    },

    /** Function: c
     *  Add a child to the current element and make it the new current
     *  element.
     *
     *  This function moves the current element pointer to the child,
     *  unless text is provided.  If you need to add another child, it
     *  is necessary to use up() to go back to the parent in the tree.
     *
     *  Parameters:
     *    (String) name - The name of the child.
     *    (Object) attrs - The attributes of the child in object notation.
     *    (String) text - The text to add to the child.
     *
     *  Returns:
     *    The Strophe.Builder object.
     */
    c: function (name, attrs, text)
    {
        var child = Strophe.xmlElement(name, attrs, text);
        this.node.appendChild(child);
        if (!text) {
            this.node = child;
        }
        return this;
    },

    /** Function: cnode
     *  Add a child to the current element and make it the new current
     *  element.
     *
     *  This function is the same as c() except that instead of using a
     *  name and an attributes object to create the child it uses an
     *  existing DOM element object.
     *
     *  Parameters:
     *    (XMLElement) elem - A DOM element.
     *
     *  Returns:
     *    The Strophe.Builder object.
     */
    cnode: function (elem)
    {
        var impNode;
        var xmlGen = Strophe.xmlGenerator();
        try {
            impNode = (xmlGen.importNode !== undefined);
        }
        catch (e) {
            impNode = false;
        }
        var newElem = impNode ?
                      xmlGen.importNode(elem, true) :
                      Strophe.copyElement(elem);
        this.node.appendChild(newElem);
        this.node = newElem;
        return this;
    },

    /** Function: t
     *  Add a child text element.
     *
     *  This *does not* make the child the new current element since there
     *  are no children of text elements.
     *
     *  Parameters:
     *    (String) text - The text data to append to the current element.
     *
     *  Returns:
     *    The Strophe.Builder object.
     */
    t: function (text)
    {
        var child = Strophe.xmlTextNode(text);
        this.node.appendChild(child);
        return this;
    },

    /** Function: h
     *  Replace current element contents with the HTML passed in.
     *
     *  This *does not* make the child the new current element
     *
     *  Parameters:
     *    (String) html - The html to insert as contents of current element.
     *
     *  Returns:
     *    The Strophe.Builder object.
     */
    h: function (html)
    {
        var fragment = document.createElement('body');

        // force the browser to try and fix any invalid HTML tags
        fragment.innerHTML = html;

        // copy cleaned html into an xml dom
        var xhtml = Strophe.createHtml(fragment);

        while(xhtml.childNodes.length > 0) {
            this.node.appendChild(xhtml.childNodes[0]);
        }
        return this;
    }
};

/** PrivateClass: Strophe.Handler
 *  _Private_ helper class for managing stanza handlers.
 *
 *  A Strophe.Handler encapsulates a user provided callback function to be
 *  executed when matching stanzas are received by the connection.
 *  Handlers can be either one-off or persistant depending on their
 *  return value. Returning true will cause a Handler to remain active, and
 *  returning false will remove the Handler.
 *
 *  Users will not use Strophe.Handler objects directly, but instead they
 *  will use Strophe.Connection.addHandler() and
 *  Strophe.Connection.deleteHandler().
 */

/** PrivateConstructor: Strophe.Handler
 *  Create and initialize a new Strophe.Handler.
 *
 *  Parameters:
 *    (Function) handler - A function to be executed when the handler is run.
 *    (String) ns - The namespace to match.
 *    (String) name - The element name to match.
 *    (String) type - The element type to match.
 *    (String) id - The element id attribute to match.
 *    (String) from - The element from attribute to match.
 *    (Object) options - Handler options
 *
 *  Returns:
 *    A new Strophe.Handler object.
 */
Strophe.Handler = function (handler, ns, name, type, id, from, options)
{
    this.handler = handler;
    this.ns = ns;
    this.name = name;
    this.type = type;
    this.id = id;
    this.options = options || {matchBare: false};

    // default matchBare to false if undefined
    if (!this.options.matchBare) {
        this.options.matchBare = false;
    }

    if (this.options.matchBare) {
        this.from = from ? Strophe.getBareJidFromJid(from) : null;
    } else {
        this.from = from;
    }

    // whether the handler is a user handler or a system handler
    this.user = true;
};

Strophe.Handler.prototype = {
    /** PrivateFunction: isMatch
     *  Tests if a stanza matches the Strophe.Handler.
     *
     *  Parameters:
     *    (XMLElement) elem - The XML element to test.
     *
     *  Returns:
     *    true if the stanza matches and false otherwise.
     */
    isMatch: function (elem)
    {
        var nsMatch;
        var from = null;

        if (this.options.matchBare) {
            from = Strophe.getBareJidFromJid(elem.getAttribute('from'));
        } else {
            from = elem.getAttribute('from');
        }

        nsMatch = false;
        if (!this.ns) {
            nsMatch = true;
        } else {
            var that = this;
            Strophe.forEachChild(elem, null, function (elem) {
                if (elem.getAttribute("xmlns") == that.ns) {
                    nsMatch = true;
                }
            });

            nsMatch = nsMatch || elem.getAttribute("xmlns") == this.ns;
        }

        var elem_type = elem.getAttribute("type");
        if (nsMatch &&
            (!this.name || Strophe.isTagEqual(elem, this.name)) &&
            (!this.type || (Array.isArray(this.type) ? this.type.indexOf(elem_type) != -1 : elem_type == this.type)) &&
            (!this.id || elem.getAttribute("id") == this.id) &&
            (!this.from || from == this.from)) {
                return true;
        }

        return false;
    },

    /** PrivateFunction: run
     *  Run the callback on a matching stanza.
     *
     *  Parameters:
     *    (XMLElement) elem - The DOM element that triggered the
     *      Strophe.Handler.
     *
     *  Returns:
     *    A boolean indicating if the handler should remain active.
     */
    run: function (elem)
    {
        var result = null;
        try {
            result = this.handler(elem);
        } catch (e) {
            if (e.sourceURL) {
                Strophe.fatal("error: " + this.handler +
                              " " + e.sourceURL + ":" +
                              e.line + " - " + e.name + ": " + e.message);
            } else if (e.fileName) {
                if (typeof(console) != "undefined") {
                    console.trace();
                    console.error(this.handler, " - error - ", e, e.message);
                }
                Strophe.fatal("error: " + this.handler + " " +
                              e.fileName + ":" + e.lineNumber + " - " +
                              e.name + ": " + e.message);
            } else {
                Strophe.fatal("error: " + e.message + "\n" + e.stack);
            }

            throw e;
        }

        return result;
    },

    /** PrivateFunction: toString
     *  Get a String representation of the Strophe.Handler object.
     *
     *  Returns:
     *    A String.
     */
    toString: function ()
    {
        return "{Handler: " + this.handler + "(" + this.name + "," +
            this.id + "," + this.ns + ")}";
    }
};

/** PrivateClass: Strophe.TimedHandler
 *  _Private_ helper class for managing timed handlers.
 *
 *  A Strophe.TimedHandler encapsulates a user provided callback that
 *  should be called after a certain period of time or at regular
 *  intervals.  The return value of the callback determines whether the
 *  Strophe.TimedHandler will continue to fire.
 *
 *  Users will not use Strophe.TimedHandler objects directly, but instead
 *  they will use Strophe.Connection.addTimedHandler() and
 *  Strophe.Connection.deleteTimedHandler().
 */

/** PrivateConstructor: Strophe.TimedHandler
 *  Create and initialize a new Strophe.TimedHandler object.
 *
 *  Parameters:
 *    (Integer) period - The number of milliseconds to wait before the
 *      handler is called.
 *    (Function) handler - The callback to run when the handler fires.  This
 *      function should take no arguments.
 *
 *  Returns:
 *    A new Strophe.TimedHandler object.
 */
Strophe.TimedHandler = function (period, handler)
{
    this.period = period;
    this.handler = handler;

    this.lastCalled = new Date().getTime();
    this.user = true;
};

Strophe.TimedHandler.prototype = {
    /** PrivateFunction: run
     *  Run the callback for the Strophe.TimedHandler.
     *
     *  Returns:
     *    true if the Strophe.TimedHandler should be called again, and false
     *      otherwise.
     */
    run: function ()
    {
        this.lastCalled = new Date().getTime();
        return this.handler();
    },

    /** PrivateFunction: reset
     *  Reset the last called time for the Strophe.TimedHandler.
     */
    reset: function ()
    {
        this.lastCalled = new Date().getTime();
    },

    /** PrivateFunction: toString
     *  Get a string representation of the Strophe.TimedHandler object.
     *
     *  Returns:
     *    The string representation.
     */
    toString: function ()
    {
        return "{TimedHandler: " + this.handler + "(" + this.period +")}";
    }
};

/** Class: Strophe.Connection
 *  XMPP Connection manager.
 *
 *  This class is the main part of Strophe.  It manages a BOSH connection
 *  to an XMPP server and dispatches events to the user callbacks as
 *  data arrives.  It supports SASL PLAIN, SASL DIGEST-MD5, SASL SCRAM-SHA1
 *  and legacy authentication.
 *
 *  After creating a Strophe.Connection object, the user will typically
 *  call connect() with a user supplied callback to handle connection level
 *  events like authentication failure, disconnection, or connection
 *  complete.
 *
 *  The user will also have several event handlers defined by using
 *  addHandler() and addTimedHandler().  These will allow the user code to
 *  respond to interesting stanzas or do something periodically with the
 *  connection.  These handlers will be active once authentication is
 *  finished.
 *
 *  To send data to the connection, use send().
 */

/** Constructor: Strophe.Connection
 *  Create and initialize a Strophe.Connection object.
 *
 *  The transport-protocol for this connection will be chosen automatically
 *  based on the given service parameter. URLs starting with "ws://" or
 *  "wss://" will use WebSockets, URLs starting with "http://", "https://"
 *  or without a protocol will use BOSH.
 *
 *  To make Strophe connect to the current host you can leave out the protocol
 *  and host part and just pass the path, e.g.
 *
 *  > var conn = new Strophe.Connection("/http-bind/");
 *
 *  WebSocket options:
 *
 *  If you want to connect to the current host with a WebSocket connection you
 *  can tell Strophe to use WebSockets through a "protocol" attribute in the
 *  optional options parameter. Valid values are "ws" for WebSocket and "wss"
 *  for Secure WebSocket.
 *  So to connect to "wss://CURRENT_HOSTNAME/xmpp-websocket" you would call
 *
 *  > var conn = new Strophe.Connection("/xmpp-websocket/", {protocol: "wss"});
 *
 *  Note that relative URLs _NOT_ starting with a "/" will also include the path
 *  of the current site.
 *
 *  Also because downgrading security is not permitted by browsers, when using
 *  relative URLs both BOSH and WebSocket connections will use their secure
 *  variants if the current connection to the site is also secure (https).
 *
 *  BOSH options:
 *
 *  by adding "sync" to the options, you can control if requests will
 *  be made synchronously or not. The default behaviour is asynchronous.
 *  If you want to make requests synchronous, make "sync" evaluate to true:
 *  > var conn = new Strophe.Connection("/http-bind/", {sync: true});
 *  You can also toggle this on an already established connection:
 *  > conn.options.sync = true;
 *
 *
 *  Parameters:
 *    (String) service - The BOSH or WebSocket service URL.
 *    (Object) options - A hash of configuration options
 *
 *  Returns:
 *    A new Strophe.Connection object.
 */
Strophe.Connection = function (service, options)
{
    // The service URL
    this.service = service;

    // Configuration options
    this.options = options || {};
    var proto = this.options.protocol || "";

    // Select protocal based on service or options
    if (service.indexOf("ws:") === 0 || service.indexOf("wss:") === 0 ||
            proto.indexOf("ws") === 0) {
        this._proto = new Strophe.Websocket(this);
    } else {
        this._proto = new Strophe.Bosh(this);
    }
    /* The connected JID. */
    this.jid = "";
    /* the JIDs domain */
    this.domain = null;
    /* stream:features */
    this.features = null;

    // SASL
    this._sasl_data = {};
    this.do_session = false;
    this.do_bind = false;

    // handler lists
    this.timedHandlers = [];
    this.handlers = [];
    this.removeTimeds = [];
    this.removeHandlers = [];
    this.addTimeds = [];
    this.addHandlers = [];

    this._authentication = {};
    this._idleTimeout = null;
    this._disconnectTimeout = null;

    this.do_authentication = true;
    this.authenticated = false;
    this.disconnecting = false;
    this.connected = false;

    this.paused = false;

    this._data = [];
    this._uniqueId = 0;

    this._sasl_success_handler = null;
    this._sasl_failure_handler = null;
    this._sasl_challenge_handler = null;

    // Max retries before disconnecting
    this.maxRetries = 5;

    // setup onIdle callback every 1/10th of a second
    this._idleTimeout = setTimeout(this._onIdle.bind(this), 100);

    // initialize plugins
    for (var k in Strophe._connectionPlugins) {
        if (Strophe._connectionPlugins.hasOwnProperty(k)) {
            var ptype = Strophe._connectionPlugins[k];
            // jslint complaints about the below line, but this is fine
            var F = function () {}; // jshint ignore:line
            F.prototype = ptype;
            this[k] = new F();
            this[k].init(this);
        }
    }
};

Strophe.Connection.prototype = {
    /** Function: reset
     *  Reset the connection.
     *
     *  This function should be called after a connection is disconnected
     *  before that connection is reused.
     */
    reset: function ()
    {
        this._proto._reset();

        // SASL
        this.do_session = false;
        this.do_bind = false;

        // handler lists
        this.timedHandlers = [];
        this.handlers = [];
        this.removeTimeds = [];
        this.removeHandlers = [];
        this.addTimeds = [];
        this.addHandlers = [];
        this._authentication = {};

        this.authenticated = false;
        this.disconnecting = false;
        this.connected = false;

        this._data = [];
        this._requests = [];
        this._uniqueId = 0;
    },

    /** Function: pause
     *  Pause the request manager.
     *
     *  This will prevent Strophe from sending any more requests to the
     *  server.  This is very useful for temporarily pausing
     *  BOSH-Connections while a lot of send() calls are happening quickly.
     *  This causes Strophe to send the data in a single request, saving
     *  many request trips.
     */
    pause: function ()
    {
        this.paused = true;
    },

    /** Function: resume
     *  Resume the request manager.
     *
     *  This resumes after pause() has been called.
     */
    resume: function ()
    {
        this.paused = false;
    },

    /** Function: getUniqueId
     *  Generate a unique ID for use in <iq/> elements.
     *
     *  All <iq/> stanzas are required to have unique id attributes.  This
     *  function makes creating these easy.  Each connection instance has
     *  a counter which starts from zero, and the value of this counter
     *  plus a colon followed by the suffix becomes the unique id. If no
     *  suffix is supplied, the counter is used as the unique id.
     *
     *  Suffixes are used to make debugging easier when reading the stream
     *  data, and their use is recommended.  The counter resets to 0 for
     *  every new connection for the same reason.  For connections to the
     *  same server that authenticate the same way, all the ids should be
     *  the same, which makes it easy to see changes.  This is useful for
     *  automated testing as well.
     *
     *  Parameters:
     *    (String) suffix - A optional suffix to append to the id.
     *
     *  Returns:
     *    A unique string to be used for the id attribute.
     */
    getUniqueId: function (suffix)
    {
        if (typeof(suffix) == "string" || typeof(suffix) == "number") {
            return ++this._uniqueId + ":" + suffix;
        } else {
            return ++this._uniqueId + "";
        }
    },

    /** Function: connect
     *  Starts the connection process.
     *
     *  As the connection process proceeds, the user supplied callback will
     *  be triggered multiple times with status updates.  The callback
     *  should take two arguments - the status code and the error condition.
     *
     *  The status code will be one of the values in the Strophe.Status
     *  constants.  The error condition will be one of the conditions
     *  defined in RFC 3920 or the condition 'strophe-parsererror'.
     *
     *  The Parameters _wait_, _hold_ and _route_ are optional and only relevant
     *  for BOSH connections. Please see XEP 124 for a more detailed explanation
     *  of the optional parameters.
     *
     *  Parameters:
     *    (String) jid - The user's JID.  This may be a bare JID,
     *      or a full JID.  If a node is not supplied, SASL ANONYMOUS
     *      authentication will be attempted.
     *    (String) pass - The user's password.
     *    (Function) callback - The connect callback function.
     *    (Integer) wait - The optional HTTPBIND wait value.  This is the
     *      time the server will wait before returning an empty result for
     *      a request.  The default setting of 60 seconds is recommended.
     *    (Integer) hold - The optional HTTPBIND hold value.  This is the
     *      number of connections the server will hold at one time.  This
     *      should almost always be set to 1 (the default).
     *    (String) route - The optional route value.
     */
    connect: function (jid, pass, callback, wait, hold, route)
    {
        this.jid = jid;
        /** Variable: authzid
         *  Authorization identity.
         */
        this.authzid = Strophe.getBareJidFromJid(this.jid);
        /** Variable: authcid
         *  Authentication identity (User name).
         */
        this.authcid = Strophe.getNodeFromJid(this.jid);
        /** Variable: pass
         *  Authentication identity (User password).
         */
        this.pass = pass;
        /** Variable: servtype
         *  Digest MD5 compatibility.
         */
        this.servtype = "xmpp";
        this.connect_callback = callback;
        this.disconnecting = false;
        this.connected = false;
        this.authenticated = false;

        // parse jid for domain
        this.domain = Strophe.getDomainFromJid(this.jid);

        this._changeConnectStatus(Strophe.Status.CONNECTING, null);

        this._proto._connect(wait, hold, route);
    },

    /** Function: attach
     *  Attach to an already created and authenticated BOSH session.
     *
     *  This function is provided to allow Strophe to attach to BOSH
     *  sessions which have been created externally, perhaps by a Web
     *  application.  This is often used to support auto-login type features
     *  without putting user credentials into the page.
     *
     *  Parameters:
     *    (String) jid - The full JID that is bound by the session.
     *    (String) sid - The SID of the BOSH session.
     *    (String) rid - The current RID of the BOSH session.  This RID
     *      will be used by the next request.
     *    (Function) callback The connect callback function.
     *    (Integer) wait - The optional HTTPBIND wait value.  This is the
     *      time the server will wait before returning an empty result for
     *      a request.  The default setting of 60 seconds is recommended.
     *      Other settings will require tweaks to the Strophe.TIMEOUT value.
     *    (Integer) hold - The optional HTTPBIND hold value.  This is the
     *      number of connections the server will hold at one time.  This
     *      should almost always be set to 1 (the default).
     *    (Integer) wind - The optional HTTBIND window value.  This is the
     *      allowed range of request ids that are valid.  The default is 5.
     */
    attach: function (jid, sid, rid, callback, wait, hold, wind)
    {
        this._proto._attach(jid, sid, rid, callback, wait, hold, wind);
    },

    /** Function: xmlInput
     *  User overrideable function that receives XML data coming into the
     *  connection.
     *
     *  The default function does nothing.  User code can override this with
     *  > Strophe.Connection.xmlInput = function (elem) {
     *  >   (user code)
     *  > };
     *
     *  Due to limitations of current Browsers' XML-Parsers the opening and closing
     *  <stream> tag for WebSocket-Connoctions will be passed as selfclosing here.
     *
     *  BOSH-Connections will have all stanzas wrapped in a <body> tag. See
     *  <Strophe.Bosh.strip> if you want to strip this tag.
     *
     *  Parameters:
     *    (XMLElement) elem - The XML data received by the connection.
     */
    /* jshint unused:false */
    xmlInput: function (elem)
    {
        return;
    },
    /* jshint unused:true */

    /** Function: xmlOutput
     *  User overrideable function that receives XML data sent to the
     *  connection.
     *
     *  The default function does nothing.  User code can override this with
     *  > Strophe.Connection.xmlOutput = function (elem) {
     *  >   (user code)
     *  > };
     *
     *  Due to limitations of current Browsers' XML-Parsers the opening and closing
     *  <stream> tag for WebSocket-Connoctions will be passed as selfclosing here.
     *
     *  BOSH-Connections will have all stanzas wrapped in a <body> tag. See
     *  <Strophe.Bosh.strip> if you want to strip this tag.
     *
     *  Parameters:
     *    (XMLElement) elem - The XMLdata sent by the connection.
     */
    /* jshint unused:false */
    xmlOutput: function (elem)
    {
        return;
    },
    /* jshint unused:true */

    /** Function: rawInput
     *  User overrideable function that receives raw data coming into the
     *  connection.
     *
     *  The default function does nothing.  User code can override this with
     *  > Strophe.Connection.rawInput = function (data) {
     *  >   (user code)
     *  > };
     *
     *  Parameters:
     *    (String) data - The data received by the connection.
     */
    /* jshint unused:false */
    rawInput: function (data)
    {
        return;
    },
    /* jshint unused:true */

    /** Function: rawOutput
     *  User overrideable function that receives raw data sent to the
     *  connection.
     *
     *  The default function does nothing.  User code can override this with
     *  > Strophe.Connection.rawOutput = function (data) {
     *  >   (user code)
     *  > };
     *
     *  Parameters:
     *    (String) data - The data sent by the connection.
     */
    /* jshint unused:false */
    rawOutput: function (data)
    {
        return;
    },
    /* jshint unused:true */

    /** Function: send
     *  Send a stanza.
     *
     *  This function is called to push data onto the send queue to
     *  go out over the wire.  Whenever a request is sent to the BOSH
     *  server, all pending data is sent and the queue is flushed.
     *
     *  Parameters:
     *    (XMLElement |
     *     [XMLElement] |
     *     Strophe.Builder) elem - The stanza to send.
     */
    send: function (elem)
    {
        if (elem === null) { return ; }
        if (typeof(elem.sort) === "function") {
            for (var i = 0; i < elem.length; i++) {
                this._queueData(elem[i]);
            }
        } else if (typeof(elem.tree) === "function") {
            this._queueData(elem.tree());
        } else {
            this._queueData(elem);
        }

        this._proto._send();
    },

    /** Function: flush
     *  Immediately send any pending outgoing data.
     *
     *  Normally send() queues outgoing data until the next idle period
     *  (100ms), which optimizes network use in the common cases when
     *  several send()s are called in succession. flush() can be used to
     *  immediately send all pending data.
     */
    flush: function ()
    {
        // cancel the pending idle period and run the idle function
        // immediately
        clearTimeout(this._idleTimeout);
        this._onIdle();
    },

    /** Function: sendIQ
     *  Helper function to send IQ stanzas.
     *
     *  Parameters:
     *    (XMLElement) elem - The stanza to send.
     *    (Function) callback - The callback function for a successful request.
     *    (Function) errback - The callback function for a failed or timed
     *      out request.  On timeout, the stanza will be null.
     *    (Integer) timeout - The time specified in milliseconds for a
     *      timeout to occur.
     *
     *  Returns:
     *    The id used to send the IQ.
    */
    sendIQ: function(elem, callback, errback, timeout) {
        var timeoutHandler = null;
        var that = this;

        if (typeof(elem.tree) === "function") {
            elem = elem.tree();
        }
        var id = elem.getAttribute('id');

        // inject id if not found
        if (!id) {
            id = this.getUniqueId("sendIQ");
            elem.setAttribute("id", id);
        }

        var expectedFrom = elem.getAttribute("to");
        var fulljid = this.jid;

        var handler = this.addHandler(function (stanza) {
            // remove timeout handler if there is one
            if (timeoutHandler) {
                that.deleteTimedHandler(timeoutHandler);
            }

            var acceptable = false;
            var from = stanza.getAttribute("from");
            if (from === expectedFrom ||
               (expectedFrom === null &&
                   (from === Strophe.getBareJidFromJid(fulljid) ||
                    from === Strophe.getDomainFromJid(fulljid) ||
                    from === fulljid))) {
                acceptable = true;
            }

            if (!acceptable) {
                throw {
                    name: "StropheError",
                    message: "Got answer to IQ from wrong jid:" + from +
                             "\nExpected jid: " + expectedFrom
                };
            }

            var iqtype = stanza.getAttribute('type');
            if (iqtype == 'result') {
                if (callback) {
                    callback(stanza);
                }
            } else if (iqtype == 'error') {
                if (errback) {
                    errback(stanza);
                }
            } else {
                throw {
                    name: "StropheError",
                    message: "Got bad IQ type of " + iqtype
                };
            }
        }, null, 'iq', ['error', 'result'], id);

        // if timeout specified, setup timeout handler.
        if (timeout) {
            timeoutHandler = this.addTimedHandler(timeout, function () {
                // get rid of normal handler
                that.deleteHandler(handler);
                // call errback on timeout with null stanza
                if (errback) {
                    errback(null);
                }
                return false;
            });
        }
        this.send(elem);
        return id;
    },

    /** PrivateFunction: _queueData
     *  Queue outgoing data for later sending.  Also ensures that the data
     *  is a DOMElement.
     */
    _queueData: function (element) {
        if (element === null ||
            !element.tagName ||
            !element.childNodes) {
            throw {
                name: "StropheError",
                message: "Cannot queue non-DOMElement."
            };
        }

        this._data.push(element);
    },

    /** PrivateFunction: _sendRestart
     *  Send an xmpp:restart stanza.
     */
    _sendRestart: function ()
    {
        this._data.push("restart");

        this._proto._sendRestart();

        this._idleTimeout = setTimeout(this._onIdle.bind(this), 100);
    },

    /** Function: addTimedHandler
     *  Add a timed handler to the connection.
     *
     *  This function adds a timed handler.  The provided handler will
     *  be called every period milliseconds until it returns false,
     *  the connection is terminated, or the handler is removed.  Handlers
     *  that wish to continue being invoked should return true.
     *
     *  Because of method binding it is necessary to save the result of
     *  this function if you wish to remove a handler with
     *  deleteTimedHandler().
     *
     *  Note that user handlers are not active until authentication is
     *  successful.
     *
     *  Parameters:
     *    (Integer) period - The period of the handler.
     *    (Function) handler - The callback function.
     *
     *  Returns:
     *    A reference to the handler that can be used to remove it.
     */
    addTimedHandler: function (period, handler)
    {
        var thand = new Strophe.TimedHandler(period, handler);
        this.addTimeds.push(thand);
        return thand;
    },

    /** Function: deleteTimedHandler
     *  Delete a timed handler for a connection.
     *
     *  This function removes a timed handler from the connection.  The
     *  handRef parameter is *not* the function passed to addTimedHandler(),
     *  but is the reference returned from addTimedHandler().
     *
     *  Parameters:
     *    (Strophe.TimedHandler) handRef - The handler reference.
     */
    deleteTimedHandler: function (handRef)
    {
        // this must be done in the Idle loop so that we don't change
        // the handlers during iteration
        this.removeTimeds.push(handRef);
    },

    /** Function: addHandler
     *  Add a stanza handler for the connection.
     *
     *  This function adds a stanza handler to the connection.  The
     *  handler callback will be called for any stanza that matches
     *  the parameters.  Note that if multiple parameters are supplied,
     *  they must all match for the handler to be invoked.
     *
     *  The handler will receive the stanza that triggered it as its argument.
     *  *The handler should return true if it is to be invoked again;
     *  returning false will remove the handler after it returns.*
     *
     *  As a convenience, the ns parameters applies to the top level element
     *  and also any of its immediate children.  This is primarily to make
     *  matching /iq/query elements easy.
     *
     *  The options argument contains handler matching flags that affect how
     *  matches are determined. Currently the only flag is matchBare (a
     *  boolean). When matchBare is true, the from parameter and the from
     *  attribute on the stanza will be matched as bare JIDs instead of
     *  full JIDs. To use this, pass {matchBare: true} as the value of
     *  options. The default value for matchBare is false.
     *
     *  The return value should be saved if you wish to remove the handler
     *  with deleteHandler().
     *
     *  Parameters:
     *    (Function) handler - The user callback.
     *    (String) ns - The namespace to match.
     *    (String) name - The stanza name to match.
     *    (String) type - The stanza type attribute to match.
     *    (String) id - The stanza id attribute to match.
     *    (String) from - The stanza from attribute to match.
     *    (String) options - The handler options
     *
     *  Returns:
     *    A reference to the handler that can be used to remove it.
     */
    addHandler: function (handler, ns, name, type, id, from, options)
    {
        var hand = new Strophe.Handler(handler, ns, name, type, id, from, options);
        this.addHandlers.push(hand);
        return hand;
    },

    /** Function: deleteHandler
     *  Delete a stanza handler for a connection.
     *
     *  This function removes a stanza handler from the connection.  The
     *  handRef parameter is *not* the function passed to addHandler(),
     *  but is the reference returned from addHandler().
     *
     *  Parameters:
     *    (Strophe.Handler) handRef - The handler reference.
     */
    deleteHandler: function (handRef)
    {
        // this must be done in the Idle loop so that we don't change
        // the handlers during iteration
        this.removeHandlers.push(handRef);
        // If a handler is being deleted while it is being added,
        // prevent it from getting added
        var i = this.addHandlers.indexOf(handRef);
        if (i >= 0) {
            this.addHandlers.splice(i, 1);
        }
    },

    /** Function: disconnect
     *  Start the graceful disconnection process.
     *
     *  This function starts the disconnection process.  This process starts
     *  by sending unavailable presence and sending BOSH body of type
     *  terminate.  A timeout handler makes sure that disconnection happens
     *  even if the BOSH server does not respond.
     *  If the Connection object isn't connected, at least tries to abort all pending requests
     *  so the connection object won't generate successful requests (which were already opened).
     *
     *  The user supplied connection callback will be notified of the
     *  progress as this process happens.
     *
     *  Parameters:
     *    (String) reason - The reason the disconnect is occuring.
     */
    disconnect: function (reason)
    {
        this._changeConnectStatus(Strophe.Status.DISCONNECTING, reason);

        Strophe.info("Disconnect was called because: " + reason);
        if (this.connected) {
            var pres = false;
            this.disconnecting = true;
            if (this.authenticated) {
                pres = $pres({
                    xmlns: Strophe.NS.CLIENT,
                    type: 'unavailable'
                });
            }
            // setup timeout handler
            this._disconnectTimeout = this._addSysTimedHandler(
                3000, this._onDisconnectTimeout.bind(this));
            this._proto._disconnect(pres);
        } else {
            Strophe.info("Disconnect was called before Strophe connected to the server");
            this._proto._abortAllRequests();
        }
    },

    /** PrivateFunction: _changeConnectStatus
     *  _Private_ helper function that makes sure plugins and the user's
     *  callback are notified of connection status changes.
     *
     *  Parameters:
     *    (Integer) status - the new connection status, one of the values
     *      in Strophe.Status
     *    (String) condition - the error condition or null
     */
    _changeConnectStatus: function (status, condition)
    {
        // notify all plugins listening for status changes
        for (var k in Strophe._connectionPlugins) {
            if (Strophe._connectionPlugins.hasOwnProperty(k)) {
                var plugin = this[k];
                if (plugin.statusChanged) {
                    try {
                        plugin.statusChanged(status, condition);
                    } catch (err) {
                        Strophe.error("" + k + " plugin caused an exception " +
                                      "changing status: " + err);
                    }
                }
            }
        }

        // notify the user's callback
        if (this.connect_callback) {
            try {
                this.connect_callback(status, condition);
            } catch (e) {
                Strophe.error("User connection callback caused an " +
                              "exception: " + e);
            }
        }
    },

    /** PrivateFunction: _doDisconnect
     *  _Private_ function to disconnect.
     *
     *  This is the last piece of the disconnection logic.  This resets the
     *  connection and alerts the user's connection callback.
     */
    _doDisconnect: function ()
    {
        if (typeof this._idleTimeout == "number") {
            clearTimeout(this._idleTimeout);
        }

        // Cancel Disconnect Timeout
        if (this._disconnectTimeout !== null) {
            this.deleteTimedHandler(this._disconnectTimeout);
            this._disconnectTimeout = null;
        }

        Strophe.info("_doDisconnect was called");
        this._proto._doDisconnect();

        this.authenticated = false;
        this.disconnecting = false;

        // delete handlers
        this.handlers = [];
        this.timedHandlers = [];
        this.removeTimeds = [];
        this.removeHandlers = [];
        this.addTimeds = [];
        this.addHandlers = [];

        // tell the parent we disconnected
        this._changeConnectStatus(Strophe.Status.DISCONNECTED, null);
        this.connected = false;
    },

    /** PrivateFunction: _dataRecv
     *  _Private_ handler to processes incoming data from the the connection.
     *
     *  Except for _connect_cb handling the initial connection request,
     *  this function handles the incoming data for all requests.  This
     *  function also fires stanza handlers that match each incoming
     *  stanza.
     *
     *  Parameters:
     *    (Strophe.Request) req - The request that has data ready.
     *    (string) req - The stanza a raw string (optiona).
     */
    _dataRecv: function (req, raw)
    {
        Strophe.info("_dataRecv called");
        var elem = this._proto._reqToData(req);
        if (elem === null) { return; }

        if (this.xmlInput !== Strophe.Connection.prototype.xmlInput) {
            if (elem.nodeName === this._proto.strip && elem.childNodes.length) {
                this.xmlInput(elem.childNodes[0]);
            } else {
                this.xmlInput(elem);
            }
        }
        if (this.rawInput !== Strophe.Connection.prototype.rawInput) {
            if (raw) {
                this.rawInput(raw);
            } else {
                this.rawInput(Strophe.serialize(elem));
            }
        }

        // remove handlers scheduled for deletion
        var i, hand;
        while (this.removeHandlers.length > 0) {
            hand = this.removeHandlers.pop();
            i = this.handlers.indexOf(hand);
            if (i >= 0) {
                this.handlers.splice(i, 1);
            }
        }

        // add handlers scheduled for addition
        while (this.addHandlers.length > 0) {
            this.handlers.push(this.addHandlers.pop());
        }

        // handle graceful disconnect
        if (this.disconnecting && this._proto._emptyQueue()) {
            this._doDisconnect();
            return;
        }

        var type = elem.getAttribute("type");
        var cond, conflict;
        if (type !== null && type == "terminate") {
            // Don't process stanzas that come in after disconnect
            if (this.disconnecting) {
                return;
            }

            // an error occurred
            cond = elem.getAttribute("condition");
            conflict = elem.getElementsByTagName("conflict");
            if (cond !== null) {
                if (cond == "remote-stream-error" && conflict.length > 0) {
                    cond = "conflict";
                }
                this._changeConnectStatus(Strophe.Status.CONNFAIL, cond);
            } else {
                this._changeConnectStatus(Strophe.Status.CONNFAIL, "unknown");
            }
            this._doDisconnect();
            return;
        }

        // send each incoming stanza through the handler chain
        var that = this;
        Strophe.forEachChild(elem, null, function (child) {
            var i, newList;
            // process handlers
            newList = that.handlers;
            that.handlers = [];
            for (i = 0; i < newList.length; i++) {
                var hand = newList[i];
                // encapsulate 'handler.run' not to lose the whole handler list if
                // one of the handlers throws an exception
                try {
                    if (hand.isMatch(child) &&
                        (that.authenticated || !hand.user)) {
                        if (hand.run(child)) {
                            that.handlers.push(hand);
                        }
                    } else {
                        that.handlers.push(hand);
                    }
                } catch(e) {
                    // if the handler throws an exception, we consider it as false
                    Strophe.warn('Removing Strophe handlers due to uncaught exception: ' + e.message);
                }
            }
        });
    },


    /** Attribute: mechanisms
     *  SASL Mechanisms available for Conncection.
     */
    mechanisms: {},

    /** PrivateFunction: _connect_cb
     *  _Private_ handler for initial connection request.
     *
     *  This handler is used to process the initial connection request
     *  response from the BOSH server. It is used to set up authentication
     *  handlers and start the authentication process.
     *
     *  SASL authentication will be attempted if available, otherwise
     *  the code will fall back to legacy authentication.
     *
     *  Parameters:
     *    (Strophe.Request) req - The current request.
     *    (Function) _callback - low level (xmpp) connect callback function.
     *      Useful for plugins with their own xmpp connect callback (when their)
     *      want to do something special).
     */
    _connect_cb: function (req, _callback, raw)
    {
        Strophe.info("_connect_cb was called");

        this.connected = true;

        var bodyWrap = this._proto._reqToData(req);
        if (!bodyWrap) { return; }

        if (this.xmlInput !== Strophe.Connection.prototype.xmlInput) {
            if (bodyWrap.nodeName === this._proto.strip && bodyWrap.childNodes.length) {
                this.xmlInput(bodyWrap.childNodes[0]);
            } else {
                this.xmlInput(bodyWrap);
            }
        }
        if (this.rawInput !== Strophe.Connection.prototype.rawInput) {
            if (raw) {
                this.rawInput(raw);
            } else {
                this.rawInput(Strophe.serialize(bodyWrap));
            }
        }

        var conncheck = this._proto._connect_cb(bodyWrap);
        if (conncheck === Strophe.Status.CONNFAIL) {
            return;
        }

        this._authentication.sasl_scram_sha1 = false;
        this._authentication.sasl_plain = false;
        this._authentication.sasl_digest_md5 = false;
        this._authentication.sasl_anonymous = false;

        this._authentication.legacy_auth = false;

        // Check for the stream:features tag
        var hasFeatures = bodyWrap.getElementsByTagNameNS(Strophe.NS.STREAM, "features").length > 0;
        var mechanisms = bodyWrap.getElementsByTagName("mechanism");
        var matched = [];
        var i, mech, found_authentication = false;
        if (!hasFeatures) {
            this._proto._no_auth_received(_callback);
            return;
        }
        if (mechanisms.length > 0) {
            for (i = 0; i < mechanisms.length; i++) {
                mech = Strophe.getText(mechanisms[i]);
                if (this.mechanisms[mech]) matched.push(this.mechanisms[mech]);
            }
        }
        this._authentication.legacy_auth =
            bodyWrap.getElementsByTagName("auth").length > 0;
        found_authentication = this._authentication.legacy_auth ||
            matched.length > 0;
        if (!found_authentication) {
            this._proto._no_auth_received(_callback);
            return;
        }
        if (this.do_authentication !== false)
            this.authenticate(matched);
    },

    /** Function: authenticate
     * Set up authentication
     *
     *  Contiunues the initial connection request by setting up authentication
     *  handlers and start the authentication process.
     *
     *  SASL authentication will be attempted if available, otherwise
     *  the code will fall back to legacy authentication.
     *
     */
    authenticate: function (matched)
    {
      var i;
      // Sorting matched mechanisms according to priority.
      for (i = 0; i < matched.length - 1; ++i) {
        var higher = i;
        for (var j = i + 1; j < matched.length; ++j) {
          if (matched[j].prototype.priority > matched[higher].prototype.priority) {
            higher = j;
          }
        }
        if (higher != i) {
          var swap = matched[i];
          matched[i] = matched[higher];
          matched[higher] = swap;
        }
      }

      // run each mechanism
      var mechanism_found = false;
      for (i = 0; i < matched.length; ++i) {
        if (!matched[i].test(this)) continue;

        this._sasl_success_handler = this._addSysHandler(
          this._sasl_success_cb.bind(this), null,
          "success", null, null);
        this._sasl_failure_handler = this._addSysHandler(
          this._sasl_failure_cb.bind(this), null,
          "failure", null, null);
        this._sasl_challenge_handler = this._addSysHandler(
          this._sasl_challenge_cb.bind(this), null,
          "challenge", null, null);

        this._sasl_mechanism = new matched[i]();
        this._sasl_mechanism.onStart(this);

        var request_auth_exchange = $build("auth", {
          xmlns: Strophe.NS.SASL,
          mechanism: this._sasl_mechanism.name
        });

        if (this._sasl_mechanism.isClientFirst) {
          var response = this._sasl_mechanism.onChallenge(this, null);
          request_auth_exchange.t(Base64.encode(response));
        }

        this.send(request_auth_exchange.tree());

        mechanism_found = true;
        break;
      }

      if (!mechanism_found) {
        // if none of the mechanism worked
        if (Strophe.getNodeFromJid(this.jid) === null) {
            // we don't have a node, which is required for non-anonymous
            // client connections
            this._changeConnectStatus(Strophe.Status.CONNFAIL,
                                      'x-strophe-bad-non-anon-jid');
            this.disconnect('x-strophe-bad-non-anon-jid');
        } else {
          // fall back to legacy authentication
          this._changeConnectStatus(Strophe.Status.AUTHENTICATING, null);
          this._addSysHandler(this._auth1_cb.bind(this), null, null,
                              null, "_auth_1");

          this.send($iq({
            type: "get",
            to: this.domain,
            id: "_auth_1"
          }).c("query", {
            xmlns: Strophe.NS.AUTH
          }).c("username", {}).t(Strophe.getNodeFromJid(this.jid)).tree());
        }
      }

    },

    _sasl_challenge_cb: function(elem) {
      var challenge = Base64.decode(Strophe.getText(elem));
      var response = this._sasl_mechanism.onChallenge(this, challenge);

      var stanza = $build('response', {
          xmlns: Strophe.NS.SASL
      });
      if (response !== "") {
        stanza.t(Base64.encode(response));
      }
      this.send(stanza.tree());

      return true;
    },

    /** PrivateFunction: _auth1_cb
     *  _Private_ handler for legacy authentication.
     *
     *  This handler is called in response to the initial <iq type='get'/>
     *  for legacy authentication.  It builds an authentication <iq/> and
     *  sends it, creating a handler (calling back to _auth2_cb()) to
     *  handle the result
     *
     *  Parameters:
     *    (XMLElement) elem - The stanza that triggered the callback.
     *
     *  Returns:
     *    false to remove the handler.
     */
    /* jshint unused:false */
    _auth1_cb: function (elem)
    {
        // build plaintext auth iq
        var iq = $iq({type: "set", id: "_auth_2"})
            .c('query', {xmlns: Strophe.NS.AUTH})
            .c('username', {}).t(Strophe.getNodeFromJid(this.jid))
            .up()
            .c('password').t(this.pass);

        if (!Strophe.getResourceFromJid(this.jid)) {
            // since the user has not supplied a resource, we pick
            // a default one here.  unlike other auth methods, the server
            // cannot do this for us.
            this.jid = Strophe.getBareJidFromJid(this.jid) + '/strophe';
        }
        iq.up().c('resource', {}).t(Strophe.getResourceFromJid(this.jid));

        this._addSysHandler(this._auth2_cb.bind(this), null,
                            null, null, "_auth_2");

        this.send(iq.tree());

        return false;
    },
    /* jshint unused:true */

    /** PrivateFunction: _sasl_success_cb
     *  _Private_ handler for succesful SASL authentication.
     *
     *  Parameters:
     *    (XMLElement) elem - The matching stanza.
     *
     *  Returns:
     *    false to remove the handler.
     */
    _sasl_success_cb: function (elem)
    {
        if (this._sasl_data["server-signature"]) {
            var serverSignature;
            var success = Base64.decode(Strophe.getText(elem));
            var attribMatch = /([a-z]+)=([^,]+)(,|$)/;
            var matches = success.match(attribMatch);
            if (matches[1] == "v") {
                serverSignature = matches[2];
            }

            if (serverSignature != this._sasl_data["server-signature"]) {
              // remove old handlers
              this.deleteHandler(this._sasl_failure_handler);
              this._sasl_failure_handler = null;
              if (this._sasl_challenge_handler) {
                this.deleteHandler(this._sasl_challenge_handler);
                this._sasl_challenge_handler = null;
              }

              this._sasl_data = {};
              return this._sasl_failure_cb(null);
            }
        }

        Strophe.info("SASL authentication succeeded.");

        if(this._sasl_mechanism)
          this._sasl_mechanism.onSuccess();

        // remove old handlers
        this.deleteHandler(this._sasl_failure_handler);
        this._sasl_failure_handler = null;
        if (this._sasl_challenge_handler) {
            this.deleteHandler(this._sasl_challenge_handler);
            this._sasl_challenge_handler = null;
        }

        var streamfeature_handlers = [];
        var wrapper = function(handlers, elem) {
            while (handlers.length) {
                this.deleteHandler(handlers.pop());
            }
            this._sasl_auth1_cb.bind(this)(elem);
            return false;
        };
        streamfeature_handlers.push(this._addSysHandler(function(elem) {
            wrapper.bind(this)(streamfeature_handlers, elem);
        }.bind(this), null, "stream:features", null, null));
        streamfeature_handlers.push(this._addSysHandler(function(elem) {
            wrapper.bind(this)(streamfeature_handlers, elem);
        }.bind(this), Strophe.NS.STREAM, "features", null, null));

        // we must send an xmpp:restart now
        this._sendRestart();

        return false;
    },

    /** PrivateFunction: _sasl_auth1_cb
     *  _Private_ handler to start stream binding.
     *
     *  Parameters:
     *    (XMLElement) elem - The matching stanza.
     *
     *  Returns:
     *    false to remove the handler.
     */
    _sasl_auth1_cb: function (elem)
    {
        // save stream:features for future usage
        this.features = elem;

        var i, child;

        for (i = 0; i < elem.childNodes.length; i++) {
            child = elem.childNodes[i];
            if (child.nodeName == 'bind') {
                this.do_bind = true;
            }

            if (child.nodeName == 'session') {
                this.do_session = true;
            }
        }

        if (!this.do_bind) {
            this._changeConnectStatus(Strophe.Status.AUTHFAIL, null);
            return false;
        } else {
            this._addSysHandler(this._sasl_bind_cb.bind(this), null, null,
                                null, "_bind_auth_2");

            var resource = Strophe.getResourceFromJid(this.jid);
            if (resource) {
                this.send($iq({type: "set", id: "_bind_auth_2"})
                          .c('bind', {xmlns: Strophe.NS.BIND})
                          .c('resource', {}).t(resource).tree());
            } else {
                this.send($iq({type: "set", id: "_bind_auth_2"})
                          .c('bind', {xmlns: Strophe.NS.BIND})
                          .tree());
            }
        }

        return false;
    },

    /** PrivateFunction: _sasl_bind_cb
     *  _Private_ handler for binding result and session start.
     *
     *  Parameters:
     *    (XMLElement) elem - The matching stanza.
     *
     *  Returns:
     *    false to remove the handler.
     */
    _sasl_bind_cb: function (elem)
    {
        if (elem.getAttribute("type") == "error") {
            Strophe.info("SASL binding failed.");
            var conflict = elem.getElementsByTagName("conflict"), condition;
            if (conflict.length > 0) {
                condition = 'conflict';
            }
            this._changeConnectStatus(Strophe.Status.AUTHFAIL, condition);
            return false;
        }

        // TODO - need to grab errors
        var bind = elem.getElementsByTagName("bind");
        var jidNode;
        if (bind.length > 0) {
            // Grab jid
            jidNode = bind[0].getElementsByTagName("jid");
            if (jidNode.length > 0) {
                this.jid = Strophe.getText(jidNode[0]);

                if (this.do_session) {
                    this._addSysHandler(this._sasl_session_cb.bind(this),
                                        null, null, null, "_session_auth_2");

                    this.send($iq({type: "set", id: "_session_auth_2"})
                                  .c('session', {xmlns: Strophe.NS.SESSION})
                                  .tree());
                } else {
                    this.authenticated = true;
                    this._changeConnectStatus(Strophe.Status.CONNECTED, null);
                }
            }
        } else {
            Strophe.info("SASL binding failed.");
            this._changeConnectStatus(Strophe.Status.AUTHFAIL, null);
            return false;
        }
    },

    /** PrivateFunction: _sasl_session_cb
     *  _Private_ handler to finish successful SASL connection.
     *
     *  This sets Connection.authenticated to true on success, which
     *  starts the processing of user handlers.
     *
     *  Parameters:
     *    (XMLElement) elem - The matching stanza.
     *
     *  Returns:
     *    false to remove the handler.
     */
    _sasl_session_cb: function (elem)
    {
        if (elem.getAttribute("type") == "result") {
            this.authenticated = true;
            this._changeConnectStatus(Strophe.Status.CONNECTED, null);
        } else if (elem.getAttribute("type") == "error") {
            Strophe.info("Session creation failed.");
            this._changeConnectStatus(Strophe.Status.AUTHFAIL, null);
            return false;
        }

        return false;
    },

    /** PrivateFunction: _sasl_failure_cb
     *  _Private_ handler for SASL authentication failure.
     *
     *  Parameters:
     *    (XMLElement) elem - The matching stanza.
     *
     *  Returns:
     *    false to remove the handler.
     */
    /* jshint unused:false */
    _sasl_failure_cb: function (elem)
    {
        // delete unneeded handlers
        if (this._sasl_success_handler) {
            this.deleteHandler(this._sasl_success_handler);
            this._sasl_success_handler = null;
        }
        if (this._sasl_challenge_handler) {
            this.deleteHandler(this._sasl_challenge_handler);
            this._sasl_challenge_handler = null;
        }

        if(this._sasl_mechanism)
          this._sasl_mechanism.onFailure();
        this._changeConnectStatus(Strophe.Status.AUTHFAIL, null);
        return false;
    },
    /* jshint unused:true */

    /** PrivateFunction: _auth2_cb
     *  _Private_ handler to finish legacy authentication.
     *
     *  This handler is called when the result from the jabber:iq:auth
     *  <iq/> stanza is returned.
     *
     *  Parameters:
     *    (XMLElement) elem - The stanza that triggered the callback.
     *
     *  Returns:
     *    false to remove the handler.
     */
    _auth2_cb: function (elem)
    {
        if (elem.getAttribute("type") == "result") {
            this.authenticated = true;
            this._changeConnectStatus(Strophe.Status.CONNECTED, null);
        } else if (elem.getAttribute("type") == "error") {
            this._changeConnectStatus(Strophe.Status.AUTHFAIL, null);
            this.disconnect('authentication failed');
        }

        return false;
    },

    /** PrivateFunction: _addSysTimedHandler
     *  _Private_ function to add a system level timed handler.
     *
     *  This function is used to add a Strophe.TimedHandler for the
     *  library code.  System timed handlers are allowed to run before
     *  authentication is complete.
     *
     *  Parameters:
     *    (Integer) period - The period of the handler.
     *    (Function) handler - The callback function.
     */
    _addSysTimedHandler: function (period, handler)
    {
        var thand = new Strophe.TimedHandler(period, handler);
        thand.user = false;
        this.addTimeds.push(thand);
        return thand;
    },

    /** PrivateFunction: _addSysHandler
     *  _Private_ function to add a system level stanza handler.
     *
     *  This function is used to add a Strophe.Handler for the
     *  library code.  System stanza handlers are allowed to run before
     *  authentication is complete.
     *
     *  Parameters:
     *    (Function) handler - The callback function.
     *    (String) ns - The namespace to match.
     *    (String) name - The stanza name to match.
     *    (String) type - The stanza type attribute to match.
     *    (String) id - The stanza id attribute to match.
     */
    _addSysHandler: function (handler, ns, name, type, id)
    {
        var hand = new Strophe.Handler(handler, ns, name, type, id);
        hand.user = false;
        this.addHandlers.push(hand);
        return hand;
    },

    /** PrivateFunction: _onDisconnectTimeout
     *  _Private_ timeout handler for handling non-graceful disconnection.
     *
     *  If the graceful disconnect process does not complete within the
     *  time allotted, this handler finishes the disconnect anyway.
     *
     *  Returns:
     *    false to remove the handler.
     */
    _onDisconnectTimeout: function ()
    {
        Strophe.info("_onDisconnectTimeout was called");

        this._proto._onDisconnectTimeout();

        // actually disconnect
        this._doDisconnect();

        return false;
    },

    /** PrivateFunction: _onIdle
     *  _Private_ handler to process events during idle cycle.
     *
     *  This handler is called every 100ms to fire timed handlers that
     *  are ready and keep poll requests going.
     */
    _onIdle: function ()
    {
        var i, thand, since, newList;

        // add timed handlers scheduled for addition
        // NOTE: we add before remove in the case a timed handler is
        // added and then deleted before the next _onIdle() call.
        while (this.addTimeds.length > 0) {
            this.timedHandlers.push(this.addTimeds.pop());
        }

        // remove timed handlers that have been scheduled for deletion
        while (this.removeTimeds.length > 0) {
            thand = this.removeTimeds.pop();
            i = this.timedHandlers.indexOf(thand);
            if (i >= 0) {
                this.timedHandlers.splice(i, 1);
            }
        }

        // call ready timed handlers
        var now = new Date().getTime();
        newList = [];
        for (i = 0; i < this.timedHandlers.length; i++) {
            thand = this.timedHandlers[i];
            if (this.authenticated || !thand.user) {
                since = thand.lastCalled + thand.period;
                if (since - now <= 0) {
                    if (thand.run()) {
                        newList.push(thand);
                    }
                } else {
                    newList.push(thand);
                }
            }
        }
        this.timedHandlers = newList;

        clearTimeout(this._idleTimeout);

        this._proto._onIdle();

        // reactivate the timer only if connected
        if (this.connected) {
            this._idleTimeout = setTimeout(this._onIdle.bind(this), 100);
        }
    }
};

/** Class: Strophe.SASLMechanism
 *
 *  encapsulates SASL authentication mechanisms.
 *
 *  User code may override the priority for each mechanism or disable it completely.
 *  See <priority> for information about changing priority and <test> for informatian on
 *  how to disable a mechanism.
 *
 *  By default, all mechanisms are enabled and the priorities are
 *
 *  SCRAM-SHA1 - 40
 *  DIGEST-MD5 - 30
 *  Plain - 20
 */

/**
 * PrivateConstructor: Strophe.SASLMechanism
 * SASL auth mechanism abstraction.
 *
 *  Parameters:
 *    (String) name - SASL Mechanism name.
 *    (Boolean) isClientFirst - If client should send response first without challenge.
 *    (Number) priority - Priority.
 *
 *  Returns:
 *    A new Strophe.SASLMechanism object.
 */
Strophe.SASLMechanism = function(name, isClientFirst, priority) {
  /** PrivateVariable: name
   *  Mechanism name.
   */
  this.name = name;
  /** PrivateVariable: isClientFirst
   *  If client sends response without initial server challenge.
   */
  this.isClientFirst = isClientFirst;
  /** Variable: priority
   *  Determines which <SASLMechanism> is chosen for authentication (Higher is better).
   *  Users may override this to prioritize mechanisms differently.
   *
   *  In the default configuration the priorities are
   *
   *  SCRAM-SHA1 - 40
   *  DIGEST-MD5 - 30
   *  Plain - 20
   *
   *  Example: (This will cause Strophe to choose the mechanism that the server sent first)
   *
   *  > Strophe.SASLMD5.priority = Strophe.SASLSHA1.priority;
   *
   *  See <SASL mechanisms> for a list of available mechanisms.
   *
   */
  this.priority = priority;
};

Strophe.SASLMechanism.prototype = {
  /**
   *  Function: test
   *  Checks if mechanism able to run.
   *  To disable a mechanism, make this return false;
   *
   *  To disable plain authentication run
   *  > Strophe.SASLPlain.test = function() {
   *  >   return false;
   *  > }
   *
   *  See <SASL mechanisms> for a list of available mechanisms.
   *
   *  Parameters:
   *    (Strophe.Connection) connection - Target Connection.
   *
   *  Returns:
   *    (Boolean) If mechanism was able to run.
   */
  /* jshint unused:false */
  test: function(connection) {
    return true;
  },
  /* jshint unused:true */

  /** PrivateFunction: onStart
   *  Called before starting mechanism on some connection.
   *
   *  Parameters:
   *    (Strophe.Connection) connection - Target Connection.
   */
  onStart: function(connection)
  {
    this._connection = connection;
  },

  /** PrivateFunction: onChallenge
   *  Called by protocol implementation on incoming challenge. If client is
   *  first (isClientFirst == true) challenge will be null on the first call.
   *
   *  Parameters:
   *    (Strophe.Connection) connection - Target Connection.
   *    (String) challenge - current challenge to handle.
   *
   *  Returns:
   *    (String) Mechanism response.
   */
  /* jshint unused:false */
  onChallenge: function(connection, challenge) {
    throw new Error("You should implement challenge handling!");
  },
  /* jshint unused:true */

  /** PrivateFunction: onFailure
   *  Protocol informs mechanism implementation about SASL failure.
   */
  onFailure: function() {
    this._connection = null;
  },

  /** PrivateFunction: onSuccess
   *  Protocol informs mechanism implementation about SASL success.
   */
  onSuccess: function() {
    this._connection = null;
  }
};

  /** Constants: SASL mechanisms
   *  Available authentication mechanisms
   *
   *  Strophe.SASLAnonymous - SASL Anonymous authentication.
   *  Strophe.SASLPlain - SASL Plain authentication.
   *  Strophe.SASLMD5 - SASL Digest-MD5 authentication
   *  Strophe.SASLSHA1 - SASL SCRAM-SHA1 authentication
   */

// Building SASL callbacks

/** PrivateConstructor: SASLAnonymous
 *  SASL Anonymous authentication.
 */
Strophe.SASLAnonymous = function() {};

Strophe.SASLAnonymous.prototype = new Strophe.SASLMechanism("ANONYMOUS", false, 10);

Strophe.SASLAnonymous.test = function(connection) {
  return connection.authcid === null;
};

Strophe.Connection.prototype.mechanisms[Strophe.SASLAnonymous.prototype.name] = Strophe.SASLAnonymous;

/** PrivateConstructor: SASLPlain
 *  SASL Plain authentication.
 */
Strophe.SASLPlain = function() {};

Strophe.SASLPlain.prototype = new Strophe.SASLMechanism("PLAIN", true, 20);

Strophe.SASLPlain.test = function(connection) {
  return connection.authcid !== null;
};

Strophe.SASLPlain.prototype.onChallenge = function(connection) {
  var auth_str = connection.authzid;
  auth_str = auth_str + "\u0000";
  auth_str = auth_str + connection.authcid;
  auth_str = auth_str + "\u0000";
  auth_str = auth_str + connection.pass;
  return auth_str;
};

Strophe.Connection.prototype.mechanisms[Strophe.SASLPlain.prototype.name] = Strophe.SASLPlain;

/** PrivateConstructor: SASLSHA1
 *  SASL SCRAM SHA 1 authentication.
 */
Strophe.SASLSHA1 = function() {};

/* TEST:
 * This is a simple example of a SCRAM-SHA-1 authentication exchange
 * when the client doesn't support channel bindings (username 'user' and
 * password 'pencil' are used):
 *
 * C: n,,n=user,r=fyko+d2lbbFgONRv9qkxdawL
 * S: r=fyko+d2lbbFgONRv9qkxdawL3rfcNHYJY1ZVvWVs7j,s=QSXCR+Q6sek8bf92,
 * i=4096
 * C: c=biws,r=fyko+d2lbbFgONRv9qkxdawL3rfcNHYJY1ZVvWVs7j,
 * p=v0X8v3Bz2T0CJGbJQyF0X+HI4Ts=
 * S: v=rmF9pqV8S7suAoZWja4dJRkFsKQ=
 *
 */

Strophe.SASLSHA1.prototype = new Strophe.SASLMechanism("SCRAM-SHA-1", true, 40);

Strophe.SASLSHA1.test = function(connection) {
  return connection.authcid !== null;
};

Strophe.SASLSHA1.prototype.onChallenge = function(connection, challenge, test_cnonce) {
  var cnonce = test_cnonce || MD5.hexdigest(Math.random() * 1234567890);

  var auth_str = "n=" + connection.authcid;
  auth_str += ",r=";
  auth_str += cnonce;

  connection._sasl_data.cnonce = cnonce;
  connection._sasl_data["client-first-message-bare"] = auth_str;

  auth_str = "n,," + auth_str;

  this.onChallenge = function (connection, challenge)
  {
    var nonce, salt, iter, Hi, U, U_old, i, k;
    var clientKey, serverKey, clientSignature;
    var responseText = "c=biws,";
    var authMessage = connection._sasl_data["client-first-message-bare"] + "," +
      challenge + ",";
    var cnonce = connection._sasl_data.cnonce;
    var attribMatch = /([a-z]+)=([^,]+)(,|$)/;

    while (challenge.match(attribMatch)) {
      var matches = challenge.match(attribMatch);
      challenge = challenge.replace(matches[0], "");
      switch (matches[1]) {
      case "r":
        nonce = matches[2];
        break;
      case "s":
        salt = matches[2];
        break;
      case "i":
        iter = matches[2];
        break;
      }
    }

    if (nonce.substr(0, cnonce.length) !== cnonce) {
      connection._sasl_data = {};
      return connection._sasl_failure_cb();
    }

    responseText += "r=" + nonce;
    authMessage += responseText;

    salt = Base64.decode(salt);
    salt += "\x00\x00\x00\x01";

    Hi = U_old = core_hmac_sha1(connection.pass, salt);
    for (i = 1; i < iter; i++) {
      U = core_hmac_sha1(connection.pass, binb2str(U_old));
      for (k = 0; k < 5; k++) {
        Hi[k] ^= U[k];
      }
      U_old = U;
    }
    Hi = binb2str(Hi);

    clientKey = core_hmac_sha1(Hi, "Client Key");
    serverKey = str_hmac_sha1(Hi, "Server Key");
    clientSignature = core_hmac_sha1(str_sha1(binb2str(clientKey)), authMessage);
    connection._sasl_data["server-signature"] = b64_hmac_sha1(serverKey, authMessage);

    for (k = 0; k < 5; k++) {
      clientKey[k] ^= clientSignature[k];
    }

    responseText += ",p=" + Base64.encode(binb2str(clientKey));

    return responseText;
  }.bind(this);

  return auth_str;
};

Strophe.Connection.prototype.mechanisms[Strophe.SASLSHA1.prototype.name] = Strophe.SASLSHA1;

/** PrivateConstructor: SASLMD5
 *  SASL DIGEST MD5 authentication.
 */
Strophe.SASLMD5 = function() {};

Strophe.SASLMD5.prototype = new Strophe.SASLMechanism("DIGEST-MD5", false, 30);

Strophe.SASLMD5.test = function(connection) {
  return connection.authcid !== null;
};

/** PrivateFunction: _quote
 *  _Private_ utility function to backslash escape and quote strings.
 *
 *  Parameters:
 *    (String) str - The string to be quoted.
 *
 *  Returns:
 *    quoted string
 */
Strophe.SASLMD5.prototype._quote = function (str)
  {
    return '"' + str.replace(/\\/g, "\\\\").replace(/"/g, '\\"') + '"';
    //" end string workaround for emacs
  };


Strophe.SASLMD5.prototype.onChallenge = function(connection, challenge, test_cnonce) {
  var attribMatch = /([a-z]+)=("[^"]+"|[^,"]+)(?:,|$)/;
  var cnonce = test_cnonce || MD5.hexdigest("" + (Math.random() * 1234567890));
  var realm = "";
  var host = null;
  var nonce = "";
  var qop = "";
  var matches;

  while (challenge.match(attribMatch)) {
    matches = challenge.match(attribMatch);
    challenge = challenge.replace(matches[0], "");
    matches[2] = matches[2].replace(/^"(.+)"$/, "$1");
    switch (matches[1]) {
    case "realm":
      realm = matches[2];
      break;
    case "nonce":
      nonce = matches[2];
      break;
    case "qop":
      qop = matches[2];
      break;
    case "host":
      host = matches[2];
      break;
    }
  }

  var digest_uri = connection.servtype + "/" + connection.domain;
  if (host !== null) {
    digest_uri = digest_uri + "/" + host;
  }

  var A1 = MD5.hash(connection.authcid +
                    ":" + realm + ":" + this._connection.pass) +
    ":" + nonce + ":" + cnonce;
  var A2 = 'AUTHENTICATE:' + digest_uri;

  var responseText = "";
  responseText += 'charset=utf-8,';
  responseText += 'username=' +
    this._quote(connection.authcid) + ',';
  responseText += 'realm=' + this._quote(realm) + ',';
  responseText += 'nonce=' + this._quote(nonce) + ',';
  responseText += 'nc=00000001,';
  responseText += 'cnonce=' + this._quote(cnonce) + ',';
  responseText += 'digest-uri=' + this._quote(digest_uri) + ',';
  responseText += 'response=' + MD5.hexdigest(MD5.hexdigest(A1) + ":" +
                                              nonce + ":00000001:" +
                                              cnonce + ":auth:" +
                                              MD5.hexdigest(A2)) + ",";
  responseText += 'qop=auth';

  this.onChallenge = function ()
  {
    return "";
  }.bind(this);

  return responseText;
};

Strophe.Connection.prototype.mechanisms[Strophe.SASLMD5.prototype.name] = Strophe.SASLMD5;


/*
    This program is distributed under the terms of the MIT license.
    Please see the LICENSE file for details.

    Copyright 2006-2008, OGG, LLC
*/

/* jshint undef: true, unused: true:, noarg: true, latedef: true */
/*global window, setTimeout, clearTimeout,
    XMLHttpRequest, ActiveXObject,
    Strophe, $build */


/** PrivateClass: Strophe.Request
 *  _Private_ helper class that provides a cross implementation abstraction
 *  for a BOSH related XMLHttpRequest.
 *
 *  The Strophe.Request class is used internally to encapsulate BOSH request
 *  information.  It is not meant to be used from user's code.
 */

/** PrivateConstructor: Strophe.Request
 *  Create and initialize a new Strophe.Request object.
 *
 *  Parameters:
 *    (XMLElement) elem - The XML data to be sent in the request.
 *    (Function) func - The function that will be called when the
 *      XMLHttpRequest readyState changes.
 *    (Integer) rid - The BOSH rid attribute associated with this request.
 *    (Integer) sends - The number of times this same request has been
 *      sent.
 */
Strophe.Request = function (elem, func, rid, sends)
{
    this.id = ++Strophe._requestId;
    this.xmlData = elem;
    this.data = Strophe.serialize(elem);
    // save original function in case we need to make a new request
    // from this one.
    this.origFunc = func;
    this.func = func;
    this.rid = rid;
    this.date = NaN;
    this.sends = sends || 0;
    this.abort = false;
    this.dead = null;

    this.age = function () {
        if (!this.date) { return 0; }
        var now = new Date();
        return (now - this.date) / 1000;
    };
    this.timeDead = function () {
        if (!this.dead) { return 0; }
        var now = new Date();
        return (now - this.dead) / 1000;
    };
    this.xhr = this._newXHR();
};

Strophe.Request.prototype = {
    /** PrivateFunction: getResponse
     *  Get a response from the underlying XMLHttpRequest.
     *
     *  This function attempts to get a response from the request and checks
     *  for errors.
     *
     *  Throws:
     *    "parsererror" - A parser error occured.
     *
     *  Returns:
     *    The DOM element tree of the response.
     */
    getResponse: function ()
    {
        var node = null;
        if (this.xhr.responseXML && this.xhr.responseXML.documentElement) {
            node = this.xhr.responseXML.documentElement;
            if (node.tagName == "parsererror") {
                Strophe.error("invalid response received");
                Strophe.error("responseText: " + this.xhr.responseText);
                Strophe.error("responseXML: " +
                              Strophe.serialize(this.xhr.responseXML));
                throw "parsererror";
            }
        } else if (this.xhr.responseText) {
            Strophe.error("invalid response received");
            Strophe.error("responseText: " + this.xhr.responseText);
            Strophe.error("responseXML: " +
                          Strophe.serialize(this.xhr.responseXML));
        }

        return node;
    },

    /** PrivateFunction: _newXHR
     *  _Private_ helper function to create XMLHttpRequests.
     *
     *  This function creates XMLHttpRequests across all implementations.
     *
     *  Returns:
     *    A new XMLHttpRequest.
     */
    _newXHR: function ()
    {
        var xhr = null;
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
            if (xhr.overrideMimeType) {
                xhr.overrideMimeType("text/xml; charset=utf-8");
            }
        } else if (window.ActiveXObject) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }

        // use Function.bind() to prepend ourselves as an argument
        xhr.onreadystatechange = this.func.bind(null, this);

        return xhr;
    }
};

/** Class: Strophe.Bosh
 *  _Private_ helper class that handles BOSH Connections
 *
 *  The Strophe.Bosh class is used internally by Strophe.Connection
 *  to encapsulate BOSH sessions. It is not meant to be used from user's code.
 */

/** File: bosh.js
 *  A JavaScript library to enable BOSH in Strophejs.
 *
 *  this library uses Bidirectional-streams Over Synchronous HTTP (BOSH)
 *  to emulate a persistent, stateful, two-way connection to an XMPP server.
 *  More information on BOSH can be found in XEP 124.
 */

/** PrivateConstructor: Strophe.Bosh
 *  Create and initialize a Strophe.Bosh object.
 *
 *  Parameters:
 *    (Strophe.Connection) connection - The Strophe.Connection that will use BOSH.
 *
 *  Returns:
 *    A new Strophe.Bosh object.
 */
Strophe.Bosh = function(connection) {
    this._conn = connection;
    /* request id for body tags */
    this.rid = Math.floor(Math.random() * 4294967295);
    /* The current session ID. */
    this.sid = null;

    // default BOSH values
    this.hold = 1;
    this.wait = 60;
    this.window = 5;
    this.errors = 0;

    this._requests = [];
};

Strophe.Bosh.prototype = {
    /** Variable: strip
     *
     *  BOSH-Connections will have all stanzas wrapped in a <body> tag when
     *  passed to <Strophe.Connection.xmlInput> or <Strophe.Connection.xmlOutput>.
     *  To strip this tag, User code can set <Strophe.Bosh.strip> to "body":
     *
     *  > Strophe.Bosh.prototype.strip = "body";
     *
     *  This will enable stripping of the body tag in both
     *  <Strophe.Connection.xmlInput> and <Strophe.Connection.xmlOutput>.
     */
    strip: null,

    /** PrivateFunction: _buildBody
     *  _Private_ helper function to generate the <body/> wrapper for BOSH.
     *
     *  Returns:
     *    A Strophe.Builder with a <body/> element.
     */
    _buildBody: function ()
    {
        var bodyWrap = $build('body', {
            rid: this.rid++,
            xmlns: Strophe.NS.HTTPBIND
        });

        if (this.sid !== null) {
            bodyWrap.attrs({sid: this.sid});
        }

        return bodyWrap;
    },

    /** PrivateFunction: _reset
     *  Reset the connection.
     *
     *  This function is called by the reset function of the Strophe Connection
     */
    _reset: function ()
    {
        this.rid = Math.floor(Math.random() * 4294967295);
        this.sid = null;
        this.errors = 0;
    },

    /** PrivateFunction: _connect
     *  _Private_ function that initializes the BOSH connection.
     *
     *  Creates and sends the Request that initializes the BOSH connection.
     */
    _connect: function (wait, hold, route)
    {
        this.wait = wait || this.wait;
        this.hold = hold || this.hold;
        this.errors = 0;

        // build the body tag
        var body = this._buildBody().attrs({
            to: this._conn.domain,
            "xml:lang": "en",
            wait: this.wait,
            hold: this.hold,
            content: "text/xml; charset=utf-8",
            ver: "1.6",
            "xmpp:version": "1.0",
            "xmlns:xmpp": Strophe.NS.BOSH
        });

        if(route){
            body.attrs({
                route: route
            });
        }

        var _connect_cb = this._conn._connect_cb;

        this._requests.push(
            new Strophe.Request(body.tree(),
                                this._onRequestStateChange.bind(
                                    this, _connect_cb.bind(this._conn)),
                                body.tree().getAttribute("rid")));
        this._throttledRequestHandler();
    },

    /** PrivateFunction: _attach
     *  Attach to an already created and authenticated BOSH session.
     *
     *  This function is provided to allow Strophe to attach to BOSH
     *  sessions which have been created externally, perhaps by a Web
     *  application.  This is often used to support auto-login type features
     *  without putting user credentials into the page.
     *
     *  Parameters:
     *    (String) jid - The full JID that is bound by the session.
     *    (String) sid - The SID of the BOSH session.
     *    (String) rid - The current RID of the BOSH session.  This RID
     *      will be used by the next request.
     *    (Function) callback The connect callback function.
     *    (Integer) wait - The optional HTTPBIND wait value.  This is the
     *      time the server will wait before returning an empty result for
     *      a request.  The default setting of 60 seconds is recommended.
     *      Other settings will require tweaks to the Strophe.TIMEOUT value.
     *    (Integer) hold - The optional HTTPBIND hold value.  This is the
     *      number of connections the server will hold at one time.  This
     *      should almost always be set to 1 (the default).
     *    (Integer) wind - The optional HTTBIND window value.  This is the
     *      allowed range of request ids that are valid.  The default is 5.
     */
    _attach: function (jid, sid, rid, callback, wait, hold, wind)
    {
        this._conn.jid = jid;
        this.sid = sid;
        this.rid = rid;

        this._conn.connect_callback = callback;

        this._conn.domain = Strophe.getDomainFromJid(this._conn.jid);

        this._conn.authenticated = true;
        this._conn.connected = true;

        this.wait = wait || this.wait;
        this.hold = hold || this.hold;
        this.window = wind || this.window;

        this._conn._changeConnectStatus(Strophe.Status.ATTACHED, null);
    },

    /** PrivateFunction: _connect_cb
     *  _Private_ handler for initial connection request.
     *
     *  This handler is used to process the Bosh-part of the initial request.
     *  Parameters:
     *    (Strophe.Request) bodyWrap - The received stanza.
     */
    _connect_cb: function (bodyWrap)
    {
        var typ = bodyWrap.getAttribute("type");
        var cond, conflict;
        if (typ !== null && typ == "terminate") {
            // an error occurred
            Strophe.error("BOSH-Connection failed: " + cond);
            cond = bodyWrap.getAttribute("condition");
            conflict = bodyWrap.getElementsByTagName("conflict");
            if (cond !== null) {
                if (cond == "remote-stream-error" && conflict.length > 0) {
                    cond = "conflict";
                }
                this._conn._changeConnectStatus(Strophe.Status.CONNFAIL, cond);
            } else {
                this._conn._changeConnectStatus(Strophe.Status.CONNFAIL, "unknown");
            }
            this._conn._doDisconnect();
            return Strophe.Status.CONNFAIL;
        }

        // check to make sure we don't overwrite these if _connect_cb is
        // called multiple times in the case of missing stream:features
        if (!this.sid) {
            this.sid = bodyWrap.getAttribute("sid");
        }
        var wind = bodyWrap.getAttribute('requests');
        if (wind) { this.window = parseInt(wind, 10); }
        var hold = bodyWrap.getAttribute('hold');
        if (hold) { this.hold = parseInt(hold, 10); }
        var wait = bodyWrap.getAttribute('wait');
        if (wait) { this.wait = parseInt(wait, 10); }
    },

    /** PrivateFunction: _disconnect
     *  _Private_ part of Connection.disconnect for Bosh
     *
     *  Parameters:
     *    (Request) pres - This stanza will be sent before disconnecting.
     */
    _disconnect: function (pres)
    {
        this._sendTerminate(pres);
    },

    /** PrivateFunction: _doDisconnect
     *  _Private_ function to disconnect.
     *
     *  Resets the SID and RID.
     */
    _doDisconnect: function ()
    {
        this.sid = null;
        this.rid = Math.floor(Math.random() * 4294967295);
    },

    /** PrivateFunction: _emptyQueue
     * _Private_ function to check if the Request queue is empty.
     *
     *  Returns:
     *    True, if there are no Requests queued, False otherwise.
     */
    _emptyQueue: function ()
    {
        return this._requests.length === 0;
    },

    /** PrivateFunction: _hitError
     *  _Private_ function to handle the error count.
     *
     *  Requests are resent automatically until their error count reaches
     *  5.  Each time an error is encountered, this function is called to
     *  increment the count and disconnect if the count is too high.
     *
     *  Parameters:
     *    (Integer) reqStatus - The request status.
     */
    _hitError: function (reqStatus)
    {
        this.errors++;
        Strophe.warn("request errored, status: " + reqStatus +
                     ", number of errors: " + this.errors);
        if (this.errors > 4) {
            this._conn._onDisconnectTimeout();
        }
    },

    /** PrivateFunction: _no_auth_received
     *
     * Called on stream start/restart when no stream:features
     * has been received and sends a blank poll request.
     */
    _no_auth_received: function (_callback)
    {
        if (_callback) {
            _callback = _callback.bind(this._conn);
        } else {
            _callback = this._conn._connect_cb.bind(this._conn);
        }
        var body = this._buildBody();
        this._requests.push(
                new Strophe.Request(body.tree(),
                    this._onRequestStateChange.bind(
                        this, _callback.bind(this._conn)),
                    body.tree().getAttribute("rid")));
        this._throttledRequestHandler();
    },

    /** PrivateFunction: _onDisconnectTimeout
     *  _Private_ timeout handler for handling non-graceful disconnection.
     *
     *  Cancels all remaining Requests and clears the queue.
     */
    _onDisconnectTimeout: function () {
        this._abortAllRequests();
    },

    /** PrivateFunction: _abortAllRequests
     *  _Private_ helper function that makes sure all pending requests are aborted.
     */
    _abortAllRequests: function _abortAllRequests() {
        var req;
        while (this._requests.length > 0) {
            req = this._requests.pop();
            req.abort = true;
            req.xhr.abort();
            // jslint complains, but this is fine. setting to empty func
            // is necessary for IE6
            req.xhr.onreadystatechange = function () {}; // jshint ignore:line
        }
    },

    /** PrivateFunction: _onIdle
     *  _Private_ handler called by Strophe.Connection._onIdle
     *
     *  Sends all queued Requests or polls with empty Request if there are none.
     */
    _onIdle: function () {
        var data = this._conn._data;

        // if no requests are in progress, poll
        if (this._conn.authenticated && this._requests.length === 0 &&
            data.length === 0 && !this._conn.disconnecting) {
            Strophe.info("no requests during idle cycle, sending " +
                         "blank request");
            data.push(null);
        }

        if (this._conn.paused) {
            return;
        }

        if (this._requests.length < 2 && data.length > 0) {
            var body = this._buildBody();
            for (var i = 0; i < data.length; i++) {
                if (data[i] !== null) {
                    if (data[i] === "restart") {
                        body.attrs({
                            to: this._conn.domain,
                            "xml:lang": "en",
                            "xmpp:restart": "true",
                            "xmlns:xmpp": Strophe.NS.BOSH
                        });
                    } else {
                        body.cnode(data[i]).up();
                    }
                }
            }
            delete this._conn._data;
            this._conn._data = [];
            this._requests.push(
                new Strophe.Request(body.tree(),
                                    this._onRequestStateChange.bind(
                                        this, this._conn._dataRecv.bind(this._conn)),
                                    body.tree().getAttribute("rid")));
            this._throttledRequestHandler();
        }

        if (this._requests.length > 0) {
            var time_elapsed = this._requests[0].age();
            if (this._requests[0].dead !== null) {
                if (this._requests[0].timeDead() >
                    Math.floor(Strophe.SECONDARY_TIMEOUT * this.wait)) {
                    this._throttledRequestHandler();
                }
            }

            if (time_elapsed > Math.floor(Strophe.TIMEOUT * this.wait)) {
                Strophe.warn("Request " +
                             this._requests[0].id +
                             " timed out, over " + Math.floor(Strophe.TIMEOUT * this.wait) +
                             " seconds since last activity");
                this._throttledRequestHandler();
            }
        }
    },

    /** PrivateFunction: _onRequestStateChange
     *  _Private_ handler for Strophe.Request state changes.
     *
     *  This function is called when the XMLHttpRequest readyState changes.
     *  It contains a lot of error handling logic for the many ways that
     *  requests can fail, and calls the request callback when requests
     *  succeed.
     *
     *  Parameters:
     *    (Function) func - The handler for the request.
     *    (Strophe.Request) req - The request that is changing readyState.
     */
    _onRequestStateChange: function (func, req)
    {
        Strophe.debug("request id " + req.id +
                      "." + req.sends + " state changed to " +
                      req.xhr.readyState);

        if (req.abort) {
            req.abort = false;
            return;
        }

        // request complete
        var reqStatus;
        if (req.xhr.readyState == 4) {
            reqStatus = 0;
            try {
                reqStatus = req.xhr.status;
            } catch (e) {
                // ignore errors from undefined status attribute.  works
                // around a browser bug
            }

            if (typeof(reqStatus) == "undefined") {
                reqStatus = 0;
            }

            if (this.disconnecting) {
                if (reqStatus >= 400) {
                    this._hitError(reqStatus);
                    return;
                }
            }

            var reqIs0 = (this._requests[0] == req);
            var reqIs1 = (this._requests[1] == req);

            if ((reqStatus > 0 && reqStatus < 500) || req.sends > 5) {
                // remove from internal queue
                this._removeRequest(req);
                Strophe.debug("request id " +
                              req.id +
                              " should now be removed");
            }

            // request succeeded
            if (reqStatus == 200) {
                // if request 1 finished, or request 0 finished and request
                // 1 is over Strophe.SECONDARY_TIMEOUT seconds old, we need to
                // restart the other - both will be in the first spot, as the
                // completed request has been removed from the queue already
                if (reqIs1 ||
                    (reqIs0 && this._requests.length > 0 &&
                     this._requests[0].age() > Math.floor(Strophe.SECONDARY_TIMEOUT * this.wait))) {
                    this._restartRequest(0);
                }
                // call handler
                Strophe.debug("request id " +
                              req.id + "." +
                              req.sends + " got 200");
                func(req);
                this.errors = 0;
            } else {
                Strophe.error("request id " +
                              req.id + "." +
                              req.sends + " error " + reqStatus +
                              " happened");
                if (reqStatus === 0 ||
                    (reqStatus >= 400 && reqStatus < 600) ||
                    reqStatus >= 12000) {
                    this._hitError(reqStatus);
                    if (reqStatus >= 400 && reqStatus < 500) {
                        this._conn._changeConnectStatus(Strophe.Status.DISCONNECTING,
                                                  null);
                        this._conn._doDisconnect();
                    }
                }
            }

            if (!((reqStatus > 0 && reqStatus < 500) ||
                  req.sends > 5)) {
                this._throttledRequestHandler();
            }
        }
    },

    /** PrivateFunction: _processRequest
     *  _Private_ function to process a request in the queue.
     *
     *  This function takes requests off the queue and sends them and
     *  restarts dead requests.
     *
     *  Parameters:
     *    (Integer) i - The index of the request in the queue.
     */
    _processRequest: function (i)
    {
        var self = this;
        var req = this._requests[i];
        var reqStatus = -1;

        try {
            if (req.xhr.readyState == 4) {
                reqStatus = req.xhr.status;
            }
        } catch (e) {
            Strophe.error("caught an error in _requests[" + i +
                          "], reqStatus: " + reqStatus);
        }

        if (typeof(reqStatus) == "undefined") {
            reqStatus = -1;
        }

        // make sure we limit the number of retries
        if (req.sends > this._conn.maxRetries) {
            this._conn._onDisconnectTimeout();
            return;
        }

        var time_elapsed = req.age();
        var primaryTimeout = (!isNaN(time_elapsed) &&
                              time_elapsed > Math.floor(Strophe.TIMEOUT * this.wait));
        var secondaryTimeout = (req.dead !== null &&
                                req.timeDead() > Math.floor(Strophe.SECONDARY_TIMEOUT * this.wait));
        var requestCompletedWithServerError = (req.xhr.readyState == 4 &&
                                               (reqStatus < 1 ||
                                                reqStatus >= 500));
        if (primaryTimeout || secondaryTimeout ||
            requestCompletedWithServerError) {
            if (secondaryTimeout) {
                Strophe.error("Request " +
                              this._requests[i].id +
                              " timed out (secondary), restarting");
            }
            req.abort = true;
            req.xhr.abort();
            // setting to null fails on IE6, so set to empty function
            req.xhr.onreadystatechange = function () {};
            this._requests[i] = new Strophe.Request(req.xmlData,
                                                    req.origFunc,
                                                    req.rid,
                                                    req.sends);
            req = this._requests[i];
        }

        if (req.xhr.readyState === 0) {
            Strophe.debug("request id " + req.id +
                          "." + req.sends + " posting");

            try {
                req.xhr.open("POST", this._conn.service, this._conn.options.sync ? false : true);
                req.xhr.setRequestHeader("Content-Type", "text/xml; charset=utf-8");
            } catch (e2) {
                Strophe.error("XHR open failed.");
                if (!this._conn.connected) {
                    this._conn._changeConnectStatus(Strophe.Status.CONNFAIL,
                                              "bad-service");
                }
                this._conn.disconnect();
                return;
            }

            // Fires the XHR request -- may be invoked immediately
            // or on a gradually expanding retry window for reconnects
            var sendFunc = function () {
                req.date = new Date();
                if (self._conn.options.customHeaders){
                    var headers = self._conn.options.customHeaders;
                    for (var header in headers) {
                        if (headers.hasOwnProperty(header)) {
                            req.xhr.setRequestHeader(header, headers[header]);
                        }
                    }
                }
                req.xhr.send(req.data);
            };

            // Implement progressive backoff for reconnects --
            // First retry (send == 1) should also be instantaneous
            if (req.sends > 1) {
                // Using a cube of the retry number creates a nicely
                // expanding retry window
                var backoff = Math.min(Math.floor(Strophe.TIMEOUT * this.wait),
                                       Math.pow(req.sends, 3)) * 1000;
                setTimeout(sendFunc, backoff);
            } else {
                sendFunc();
            }

            req.sends++;

            if (this._conn.xmlOutput !== Strophe.Connection.prototype.xmlOutput) {
                if (req.xmlData.nodeName === this.strip && req.xmlData.childNodes.length) {
                    this._conn.xmlOutput(req.xmlData.childNodes[0]);
                } else {
                    this._conn.xmlOutput(req.xmlData);
                }
            }
            if (this._conn.rawOutput !== Strophe.Connection.prototype.rawOutput) {
                this._conn.rawOutput(req.data);
            }
        } else {
            Strophe.debug("_processRequest: " +
                          (i === 0 ? "first" : "second") +
                          " request has readyState of " +
                          req.xhr.readyState);
        }
    },

    /** PrivateFunction: _removeRequest
     *  _Private_ function to remove a request from the queue.
     *
     *  Parameters:
     *    (Strophe.Request) req - The request to remove.
     */
    _removeRequest: function (req)
    {
        Strophe.debug("removing request");

        var i;
        for (i = this._requests.length - 1; i >= 0; i--) {
            if (req == this._requests[i]) {
                this._requests.splice(i, 1);
            }
        }

        // IE6 fails on setting to null, so set to empty function
        req.xhr.onreadystatechange = function () {};

        this._throttledRequestHandler();
    },

    /** PrivateFunction: _restartRequest
     *  _Private_ function to restart a request that is presumed dead.
     *
     *  Parameters:
     *    (Integer) i - The index of the request in the queue.
     */
    _restartRequest: function (i)
    {
        var req = this._requests[i];
        if (req.dead === null) {
            req.dead = new Date();
        }

        this._processRequest(i);
    },

    /** PrivateFunction: _reqToData
     * _Private_ function to get a stanza out of a request.
     *
     * Tries to extract a stanza out of a Request Object.
     * When this fails the current connection will be disconnected.
     *
     *  Parameters:
     *    (Object) req - The Request.
     *
     *  Returns:
     *    The stanza that was passed.
     */
    _reqToData: function (req)
    {
        try {
            return req.getResponse();
        } catch (e) {
            if (e != "parsererror") { throw e; }
            this._conn.disconnect("strophe-parsererror");
        }
    },

    /** PrivateFunction: _sendTerminate
     *  _Private_ function to send initial disconnect sequence.
     *
     *  This is the first step in a graceful disconnect.  It sends
     *  the BOSH server a terminate body and includes an unavailable
     *  presence if authentication has completed.
     */
    _sendTerminate: function (pres)
    {
        Strophe.info("_sendTerminate was called");
        var body = this._buildBody().attrs({type: "terminate"});

        if (pres) {
            body.cnode(pres.tree());
        }

        var req = new Strophe.Request(body.tree(),
                                      this._onRequestStateChange.bind(
                                          this, this._conn._dataRecv.bind(this._conn)),
                                      body.tree().getAttribute("rid"));

        this._requests.push(req);
        this._throttledRequestHandler();
    },

    /** PrivateFunction: _send
     *  _Private_ part of the Connection.send function for BOSH
     *
     * Just triggers the RequestHandler to send the messages that are in the queue
     */
    _send: function () {
        clearTimeout(this._conn._idleTimeout);
        this._throttledRequestHandler();
        this._conn._idleTimeout = setTimeout(this._conn._onIdle.bind(this._conn), 100);
    },

    /** PrivateFunction: _sendRestart
     *
     *  Send an xmpp:restart stanza.
     */
    _sendRestart: function ()
    {
        this._throttledRequestHandler();
        clearTimeout(this._conn._idleTimeout);
    },

    /** PrivateFunction: _throttledRequestHandler
     *  _Private_ function to throttle requests to the connection window.
     *
     *  This function makes sure we don't send requests so fast that the
     *  request ids overflow the connection window in the case that one
     *  request died.
     */
    _throttledRequestHandler: function ()
    {
        if (!this._requests) {
            Strophe.debug("_throttledRequestHandler called with " +
                          "undefined requests");
        } else {
            Strophe.debug("_throttledRequestHandler called with " +
                          this._requests.length + " requests");
        }

        if (!this._requests || this._requests.length === 0) {
            return;
        }

        if (this._requests.length > 0) {
            this._processRequest(0);
        }

        if (this._requests.length > 1 &&
            Math.abs(this._requests[0].rid -
                     this._requests[1].rid) < this.window) {
            this._processRequest(1);
        }
    }
};

/*
    This program is distributed under the terms of the MIT license.
    Please see the LICENSE file for details.

    Copyright 2006-2008, OGG, LLC
*/

/* jshint undef: true, unused: true:, noarg: true, latedef: true */
/*global window, clearTimeout, WebSocket,
    DOMParser, Strophe, $build */

/** Class: Strophe.WebSocket
 *  _Private_ helper class that handles WebSocket Connections
 *
 *  The Strophe.WebSocket class is used internally by Strophe.Connection
 *  to encapsulate WebSocket sessions. It is not meant to be used from user's code.
 */

/** File: websocket.js
 *  A JavaScript library to enable XMPP over Websocket in Strophejs.
 *
 *  This file implements XMPP over WebSockets for Strophejs.
 *  If a Connection is established with a Websocket url (ws://...)
 *  Strophe will use WebSockets.
 *  For more information on XMPP-over WebSocket see this RFC draft:
 *  http://tools.ietf.org/html/draft-ietf-xmpp-websocket-00
 *
 *  WebSocket support implemented by Andreas Guth (andreas.guth@rwth-aachen.de)
 */

/** PrivateConstructor: Strophe.Websocket
 *  Create and initialize a Strophe.WebSocket object.
 *  Currently only sets the connection Object.
 *
 *  Parameters:
 *    (Strophe.Connection) connection - The Strophe.Connection that will use WebSockets.
 *
 *  Returns:
 *    A new Strophe.WebSocket object.
 */
Strophe.Websocket = function(connection) {
    this._conn = connection;
    this.strip = "wrapper";

    var service = connection.service;
    if (service.indexOf("ws:") !== 0 && service.indexOf("wss:") !== 0) {
        // If the service is not an absolute URL, assume it is a path and put the absolute
        // URL together from options, current URL and the path.
        var new_service = "";

        if (connection.options.protocol === "ws" && window.location.protocol !== "https:") {
            new_service += "ws";
        } else {
            new_service += "wss";
        }

        new_service += "://" + window.location.host;

        if (service.indexOf("/") !== 0) {
            new_service += window.location.pathname + service;
        } else {
            new_service += service;
        }

        connection.service = new_service;
    }
};

Strophe.Websocket.prototype = {
    /** PrivateFunction: _buildStream
     *  _Private_ helper function to generate the <stream> start tag for WebSockets
     *
     *  Returns:
     *    A Strophe.Builder with a <stream> element.
     */
    _buildStream: function ()
    {
        return $build("open", {
            "xmlns": Strophe.NS.FRAMING,
            "to": this._conn.domain,
            "version": '1.0'
        });
    },

    /** PrivateFunction: _check_streamerror
     * _Private_ checks a message for stream:error
     *
     *  Parameters:
     *    (Strophe.Request) bodyWrap - The received stanza.
     *    connectstatus - The ConnectStatus that will be set on error.
     *  Returns:
     *     true if there was a streamerror, false otherwise.
     */
    _check_streamerror: function (bodyWrap, connectstatus) {
        var errors = bodyWrap.getElementsByTagNameNS(Strophe.NS.STREAM, "error");
        if (errors.length === 0) {
            return false;
        }
        var error = errors[0];

        var condition = "";
        var text = "";

        var ns = "urn:ietf:params:xml:ns:xmpp-streams";
        for (var i = 0; i < error.childNodes.length; i++) {
            var e = error.childNodes[i];
            if (e.getAttribute("xmlns") !== ns) {
                break;
            } if (e.nodeName === "text") {
                text = e.textContent;
            } else {
                condition = e.nodeName;
            }
        }

        var errorString = "WebSocket stream error: ";

        if (condition) {
            errorString += condition;
        } else {
            errorString += "unknown";
        }

        if (text) {
            errorString += " - " + condition;
        }

        Strophe.error(errorString);

        // close the connection on stream_error
        this._conn._changeConnectStatus(connectstatus, condition);
        this._conn._doDisconnect();
        return true;
    },

    /** PrivateFunction: _reset
     *  Reset the connection.
     *
     *  This function is called by the reset function of the Strophe Connection.
     *  Is not needed by WebSockets.
     */
    _reset: function ()
    {
        return;
    },

    /** PrivateFunction: _connect
     *  _Private_ function called by Strophe.Connection.connect
     *
     *  Creates a WebSocket for a connection and assigns Callbacks to it.
     *  Does nothing if there already is a WebSocket.
     */
    _connect: function () {
        // Ensure that there is no open WebSocket from a previous Connection.
        this._closeSocket();

        // Create the new WobSocket
        this.socket = new WebSocket(this._conn.service, "xmpp");
        this.socket.onopen = this._onOpen.bind(this);
        this.socket.onerror = this._onError.bind(this);
        this.socket.onclose = this._onClose.bind(this);
        this.socket.onmessage = this._connect_cb_wrapper.bind(this);
    },

    /** PrivateFunction: _connect_cb
     *  _Private_ function called by Strophe.Connection._connect_cb
     *
     * checks for stream:error
     *
     *  Parameters:
     *    (Strophe.Request) bodyWrap - The received stanza.
     */
    _connect_cb: function(bodyWrap) {
        var error = this._check_streamerror(bodyWrap, Strophe.Status.CONNFAIL);
        if (error) {
            return Strophe.Status.CONNFAIL;
        }
    },

    /** PrivateFunction: _handleStreamStart
     * _Private_ function that checks the opening <open /> tag for errors.
     *
     * Disconnects if there is an error and returns false, true otherwise.
     *
     *  Parameters:
     *    (Node) message - Stanza containing the <open /> tag.
     */
    _handleStreamStart: function(message) {
        var error = false;

        // Check for errors in the <open /> tag
        var ns = message.getAttribute("xmlns");
        if (typeof ns !== "string") {
            error = "Missing xmlns in <open />";
        } else if (ns !== Strophe.NS.FRAMING) {
            error = "Wrong xmlns in <open />: " + ns;
        }

        var ver = message.getAttribute("version");
        if (typeof ver !== "string") {
            error = "Missing version in <open />";
        } else if (ver !== "1.0") {
            error = "Wrong version in <open />: " + ver;
        }

        if (error) {
            this._conn._changeConnectStatus(Strophe.Status.CONNFAIL, error);
            this._conn._doDisconnect();
            return false;
        }

        return true;
    },

    /** PrivateFunction: _connect_cb_wrapper
     * _Private_ function that handles the first connection messages.
     *
     * On receiving an opening stream tag this callback replaces itself with the real
     * message handler. On receiving a stream error the connection is terminated.
     */
    _connect_cb_wrapper: function(message) {
        if (message.data.indexOf("<open ") === 0 || message.data.indexOf("<?xml") === 0) {
            // Strip the XML Declaration, if there is one
            var data = message.data.replace(/^(<\?.*?\?>\s*)*/, "");
            if (data === '') return;

            var streamStart = new DOMParser().parseFromString(data, "text/xml").documentElement;
            this._conn.xmlInput(streamStart);
            this._conn.rawInput(message.data);

            //_handleStreamSteart will check for XML errors and disconnect on error
            if (this._handleStreamStart(streamStart)) {
                //_connect_cb will check for stream:error and disconnect on error
                this._connect_cb(streamStart);
            }
        } else if (message.data.indexOf("<close ") === 0) { //'<close xmlns="urn:ietf:params:xml:ns:xmpp-framing />') {
            this._conn.rawInput(message.data);
            this._conn.xmlInput(message);
            var see_uri = message.getAttribute("see-other-uri");
            if (see_uri) {
                this._conn._changeConnectStatus(Strophe.Status.REDIRECT, "Received see-other-uri, resetting connection");
                this._conn.reset();
                this._conn.service = see_uri;
                this._connect();
            } else {
                this._conn._changeConnectStatus(Strophe.Status.CONNFAIL, "Received closing stream");
                this._conn._doDisconnect();
            }
        } else {
            var string = this._streamWrap(message.data);
            var elem = new DOMParser().parseFromString(string, "text/xml").documentElement;
            this.socket.onmessage = this._onMessage.bind(this);
            this._conn._connect_cb(elem, null, message.data);
        }
    },

    /** PrivateFunction: _disconnect
     *  _Private_ function called by Strophe.Connection.disconnect
     *
     *  Disconnects and sends a last stanza if one is given
     *
     *  Parameters:
     *    (Request) pres - This stanza will be sent before disconnecting.
     */
    _disconnect: function (pres)
    {
        if (this.socket && this.socket.readyState !== WebSocket.CLOSED) {
            if (pres) {
                this._conn.send(pres);
            }
            var close = $build("close", { "xmlns": Strophe.NS.FRAMING, });
            this._conn.xmlOutput(close);
            var closeString = Strophe.serialize(close);
            this._conn.rawOutput(closeString);
            try {
                this.socket.send(closeString);
            } catch (e) {
                Strophe.info("Couldn't send <close /> tag.");
            }
        }
        this._conn._doDisconnect();
    },

    /** PrivateFunction: _doDisconnect
     *  _Private_ function to disconnect.
     *
     *  Just closes the Socket for WebSockets
     */
    _doDisconnect: function ()
    {
        Strophe.info("WebSockets _doDisconnect was called");
        this._closeSocket();
    },

    /** PrivateFunction _streamWrap
     *  _Private_ helper function to wrap a stanza in a <stream> tag.
     *  This is used so Strophe can process stanzas from WebSockets like BOSH
     */
    _streamWrap: function (stanza)
    {
        return "<wrapper>" + stanza + '</wrapper>';
    },


    /** PrivateFunction: _closeSocket
     *  _Private_ function to close the WebSocket.
     *
     *  Closes the socket if it is still open and deletes it
     */
    _closeSocket: function ()
    {
        if (this.socket) { try {
            this.socket.close();
        } catch (e) {} }
        this.socket = null;
    },

    /** PrivateFunction: _emptyQueue
     * _Private_ function to check if the message queue is empty.
     *
     *  Returns:
     *    True, because WebSocket messages are send immediately after queueing.
     */
    _emptyQueue: function ()
    {
        return true;
    },

    /** PrivateFunction: _onClose
     * _Private_ function to handle websockets closing.
     *
     * Nothing to do here for WebSockets
     */
    _onClose: function() {
        if(this._conn.connected && !this._conn.disconnecting) {
            Strophe.error("Websocket closed unexcectedly");
            this._conn._doDisconnect();
        } else {
            Strophe.info("Websocket closed");
        }
    },

    /** PrivateFunction: _no_auth_received
     *
     * Called on stream start/restart when no stream:features
     * has been received.
     */
    _no_auth_received: function (_callback)
    {
        Strophe.error("Server did not send any auth methods");
        this._conn._changeConnectStatus(Strophe.Status.CONNFAIL, "Server did not send any auth methods");
        if (_callback) {
            _callback = _callback.bind(this._conn);
            _callback();
        }
        this._conn._doDisconnect();
    },

    /** PrivateFunction: _onDisconnectTimeout
     *  _Private_ timeout handler for handling non-graceful disconnection.
     *
     *  This does nothing for WebSockets
     */
    _onDisconnectTimeout: function () {},

    /** PrivateFunction: _abortAllRequests
     *  _Private_ helper function that makes sure all pending requests are aborted.
     */
    _abortAllRequests: function () {},

    /** PrivateFunction: _onError
     * _Private_ function to handle websockets errors.
     *
     * Parameters:
     * (Object) error - The websocket error.
     */
    _onError: function(error) {
        Strophe.error("Websocket error " + error);
        this._conn._changeConnectStatus(Strophe.Status.CONNFAIL, "The WebSocket connection could not be established was disconnected.");
        this._disconnect();
    },

    /** PrivateFunction: _onIdle
     *  _Private_ function called by Strophe.Connection._onIdle
     *
     *  sends all queued stanzas
     */
    _onIdle: function () {
        var data = this._conn._data;
        if (data.length > 0 && !this._conn.paused) {
            for (var i = 0; i < data.length; i++) {
                if (data[i] !== null) {
                    var stanza, rawStanza;
                    if (data[i] === "restart") {
                        stanza = this._buildStream().tree();
                    } else {
                        stanza = data[i];
                    }
                    rawStanza = Strophe.serialize(stanza);
                    this._conn.xmlOutput(stanza);
                    this._conn.rawOutput(rawStanza);
                    this.socket.send(rawStanza);
                }
            }
            this._conn._data = [];
        }
    },

    /** PrivateFunction: _onMessage
     * _Private_ function to handle websockets messages.
     *
     * This function parses each of the messages as if they are full documents. [TODO : We may actually want to use a SAX Push parser].
     *
     * Since all XMPP traffic starts with "<stream:stream version='1.0' xml:lang='en' xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' id='3697395463' from='SERVER'>"
     * The first stanza will always fail to be parsed...
     * Addtionnaly, the seconds stanza will always be a <stream:features> with the stream NS defined in the previous stanza... so we need to 'force' the inclusion of the NS in this stanza!
     *
     * Parameters:
     * (string) message - The websocket message.
     */
    _onMessage: function(message) {
        var elem, data;
        // check for closing stream
        var close = '<close xmlns="urn:ietf:params:xml:ns:xmpp-framing" />';
        if (message.data === close) {
            this._conn.rawInput(close);
            this._conn.xmlInput(message);
            if (!this._conn.disconnecting) {
                this._conn._doDisconnect();
            }
            return;
        } else if (message.data.search("<open ") === 0) {
            // This handles stream restarts
            elem = new DOMParser().parseFromString(message.data, "text/xml").documentElement;

            if (!this._handleStreamStart(elem)) {
                return;
            }
        } else {
            data = this._streamWrap(message.data);
            elem = new DOMParser().parseFromString(data, "text/xml").documentElement;
        }

        if (this._check_streamerror(elem, Strophe.Status.ERROR)) {
            return;
        }

        //handle unavailable presence stanza before disconnecting
        if (this._conn.disconnecting &&
                elem.firstChild.nodeName === "presence" &&
                elem.firstChild.getAttribute("type") === "unavailable") {
            this._conn.xmlInput(elem);
            this._conn.rawInput(Strophe.serialize(elem));
            // if we are already disconnecting we will ignore the unavailable stanza and
            // wait for the </stream:stream> tag before we close the connection
            return;
        }
        this._conn._dataRecv(elem, message.data);
    },

    /** PrivateFunction: _onOpen
     * _Private_ function to handle websockets connection setup.
     *
     * The opening stream tag is sent here.
     */
    _onOpen: function() {
        Strophe.info("Websocket open");
        var start = this._buildStream();
        this._conn.xmlOutput(start.tree());

        var startString = Strophe.serialize(start);
        this._conn.rawOutput(startString);
        this.socket.send(startString);
    },

    /** PrivateFunction: _reqToData
     * _Private_ function to get a stanza out of a request.
     *
     * WebSockets don't use requests, so the passed argument is just returned.
     *
     *  Parameters:
     *    (Object) stanza - The stanza.
     *
     *  Returns:
     *    The stanza that was passed.
     */
    _reqToData: function (stanza)
    {
        return stanza;
    },

    /** PrivateFunction: _send
     *  _Private_ part of the Connection.send function for WebSocket
     *
     * Just flushes the messages that are in the queue
     */
    _send: function () {
        this._conn.flush();
    },

    /** PrivateFunction: _sendRestart
     *
     *  Send an xmpp:restart stanza.
     */
    _sendRestart: function ()
    {
        clearTimeout(this._conn._idleTimeout);
        this._conn._onIdle.bind(this._conn)();
    }
};

/* jshint ignore:start */
if (callback) {
    return callback(Strophe, $build, $msg, $iq, $pres);
}


})(function (Strophe, build, msg, iq, pres) {
    window.Strophe = Strophe;
    window.$build = build;
    window.$msg = msg;
    window.$iq = iq;
    window.$pres = pres;
});
/* jshint ignore:end */

/*二*/

/*
    json2.js
    2014-02-04

    Public Domain.

    NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.

    See http://www.JSON.org/js.html


    This code should be minified before deployment.
    See http://javascript.crockford.com/jsmin.html

    USE YOUR OWN COPY. IT IS EXTREMELY UNWISE TO LOAD CODE FROM SERVERS YOU DO
    NOT CONTROL.


    This file creates a global JSON object containing two methods: stringify
    and parse.

        JSON.stringify(value, replacer, space)
            value       any JavaScript value, usually an object or array.

            replacer    an optional parameter that determines how object
                        values are stringified for objects. It can be a
                        function or an array of strings.

            space       an optional parameter that specifies the indentation
                        of nested structures. If it is omitted, the text will
                        be packed without extra whitespace. If it is a number,
                        it will specify the number of spaces to indent at each
                        level. If it is a string (such as '\t' or '&nbsp;'),
                        it contains the characters used to indent at each level.

            This method produces a JSON text from a JavaScript value.

            When an object value is found, if the object contains a toJSON
            method, its toJSON method will be called and the result will be
            stringified. A toJSON method does not serialize: it returns the
            value represented by the name/value pair that should be serialized,
            or undefined if nothing should be serialized. The toJSON method
            will be passed the key associated with the value, and this will be
            bound to the value

            For example, this would serialize Dates as ISO strings.

                Date.prototype.toJSON = function (key) {
                    function f(n) {
                        // Format integers to have at least two digits.
                        return n < 10 ? '0' + n : n;
                    }

                    return this.getUTCFullYear()   + '-' +
                         f(this.getUTCMonth() + 1) + '-' +
                         f(this.getUTCDate())      + 'T' +
                         f(this.getUTCHours())     + ':' +
                         f(this.getUTCMinutes())   + ':' +
                         f(this.getUTCSeconds())   + 'Z';
                };

            You can provide an optional replacer method. It will be passed the
            key and value of each member, with this bound to the containing
            object. The value that is returned from your method will be
            serialized. If your method returns undefined, then the member will
            be excluded from the serialization.

            If the replacer parameter is an array of strings, then it will be
            used to select the members to be serialized. It filters the results
            such that only members with keys listed in the replacer array are
            stringified.

            Values that do not have JSON representations, such as undefined or
            functions, will not be serialized. Such values in objects will be
            dropped; in arrays they will be replaced with null. You can use
            a replacer function to replace those with JSON values.
            JSON.stringify(undefined) returns undefined.

            The optional space parameter produces a stringification of the
            value that is filled with line breaks and indentation to make it
            easier to read.

            If the space parameter is a non-empty string, then that string will
            be used for indentation. If the space parameter is a number, then
            the indentation will be that many spaces.

            Example:

            text = JSON.stringify(['e', {pluribus: 'unum'}]);
            // text is '["e",{"pluribus":"unum"}]'


            text = JSON.stringify(['e', {pluribus: 'unum'}], null, '\t');
            // text is '[\n\t"e",\n\t{\n\t\t"pluribus": "unum"\n\t}\n]'

            text = JSON.stringify([new Date()], function (key, value) {
                return this[key] instanceof Date ?
                    'Date(' + this[key] + ')' : value;
            });
            // text is '["Date(---current time---)"]'


        JSON.parse(text, reviver)
            This method parses a JSON text to produce an object or array.
            It can throw a SyntaxError exception.

            The optional reviver parameter is a function that can filter and
            transform the results. It receives each of the keys and values,
            and its return value is used instead of the original value.
            If it returns what it received, then the structure is not modified.
            If it returns undefined then the member is deleted.

            Example:

            // Parse the text. Values that look like ISO date strings will
            // be converted to Date objects.

            myData = JSON.parse(text, function (key, value) {
                var a;
                if (typeof value === 'string') {
                    a =
/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)Z$/.exec(value);
                    if (a) {
                        return new Date(Date.UTC(+a[1], +a[2] - 1, +a[3], +a[4],
                            +a[5], +a[6]));
                    }
                }
                return value;
            });

            myData = JSON.parse('["Date(09/09/2001)"]', function (key, value) {
                var d;
                if (typeof value === 'string' &&
                        value.slice(0, 5) === 'Date(' &&
                        value.slice(-1) === ')') {
                    d = new Date(value.slice(5, -1));
                    if (d) {
                        return d;
                    }
                }
                return value;
            });


    This is a reference implementation. You are free to copy, modify, or
    redistribute.
*/

/*jslint evil: true, regexp: true */

/*members "", "\b", "\t", "\n", "\f", "\r", "\"", JSON, "\\", apply,
    call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
    getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join,
    lastIndex, length, parse, prototype, push, replace, slice, stringify,
    test, toJSON, toString, valueOf
*/


// Create a JSON object only if one does not already exist. We create the
// methods in a closure to avoid creating global variables.

if (typeof JSON !== 'object') {
    JSON = {};
}

(function () {
    'use strict';

    function f(n) {
        // Format integers to have at least two digits.
        return n < 10 ? '0' + n : n;
    }

    if (typeof Date.prototype.toJSON !== 'function') {

        Date.prototype.toJSON = function () {

            return isFinite(this.valueOf())
                ? this.getUTCFullYear()     + '-' +
                    f(this.getUTCMonth() + 1) + '-' +
                    f(this.getUTCDate())      + 'T' +
                    f(this.getUTCHours())     + ':' +
                    f(this.getUTCMinutes())   + ':' +
                    f(this.getUTCSeconds())   + 'Z'
                : null;
        };

        String.prototype.toJSON      =
            Number.prototype.toJSON  =
            Boolean.prototype.toJSON = function () {
                return this.valueOf();
            };
    }

    var cx,
        escapable,
        gap,
        indent,
        meta,
        rep;


    function quote(string) {

// If the string contains no control characters, no quote characters, and no
// backslash characters, then we can safely slap some quotes around it.
// Otherwise we must also replace the offending characters with safe escape
// sequences.

        escapable.lastIndex = 0;
        return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
            var c = meta[a];
            return typeof c === 'string'
                ? c
                : '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
        }) + '"' : '"' + string + '"';
    }


    function str(key, holder) {

// Produce a string from holder[key].

        var i,          // The loop counter.
            k,          // The member key.
            v,          // The member value.
            length,
            mind = gap,
            partial,
            value = holder[key];

// If the value has a toJSON method, call it to obtain a replacement value.

        if (value && typeof value === 'object' &&
                typeof value.toJSON === 'function') {
            value = value.toJSON(key);
        }

// If we were called with a replacer function, then call the replacer to
// obtain a replacement value.

        if (typeof rep === 'function') {
            value = rep.call(holder, key, value);
        }

// What happens next depends on the value's type.

        switch (typeof value) {
        case 'string':
            return quote(value);

        case 'number':

// JSON numbers must be finite. Encode non-finite numbers as null.

            return isFinite(value) ? String(value) : 'null';

        case 'boolean':
        case 'null':

// If the value is a boolean or null, convert it to a string. Note:
// typeof null does not produce 'null'. The case is included here in
// the remote chance that this gets fixed someday.

            return String(value);

// If the type is 'object', we might be dealing with an object or an array or
// null.

        case 'object':

// Due to a specification blunder in ECMAScript, typeof null is 'object',
// so watch out for that case.

            if (!value) {
                return 'null';
            }

// Make an array to hold the partial results of stringifying this object value.

            gap += indent;
            partial = [];

// Is the value an array?

            if (Object.prototype.toString.apply(value) === '[object Array]') {

// The value is an array. Stringify every element. Use null as a placeholder
// for non-JSON values.

                length = value.length;
                for (i = 0; i < length; i += 1) {
                    partial[i] = str(i, value) || 'null';
                }

// Join all of the elements together, separated with commas, and wrap them in
// brackets.

                v = partial.length === 0
                    ? '[]'
                    : gap
                    ? '[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']'
                    : '[' + partial.join(',') + ']';
                gap = mind;
                return v;
            }

// If the replacer is an array, use it to select the members to be stringified.

            if (rep && typeof rep === 'object') {
                length = rep.length;
                for (i = 0; i < length; i += 1) {
                    if (typeof rep[i] === 'string') {
                        k = rep[i];
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            } else {

// Otherwise, iterate through all of the keys in the object.

                for (k in value) {
                    if (Object.prototype.hasOwnProperty.call(value, k)) {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            }

// Join all of the member texts together, separated with commas,
// and wrap them in braces.

            v = partial.length === 0
                ? '{}'
                : gap
                ? '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}'
                : '{' + partial.join(',') + '}';
            gap = mind;
            return v;
        }
    }

// If the JSON object does not yet have a stringify method, give it one.

    if (typeof JSON.stringify !== 'function') {
        escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
        meta = {    // table of character substitutions
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        };
        JSON.stringify = function (value, replacer, space) {

// The stringify method takes a value and an optional replacer, and an optional
// space parameter, and returns a JSON text. The replacer can be a function
// that can replace values, or an array of strings that will select the keys.
// A default replacer method can be provided. Use of the space parameter can
// produce text that is more easily readable.

            var i;
            gap = '';
            indent = '';

// If the space parameter is a number, make an indent string containing that
// many spaces.

            if (typeof space === 'number') {
                for (i = 0; i < space; i += 1) {
                    indent += ' ';
                }

// If the space parameter is a string, it will be used as the indent string.

            } else if (typeof space === 'string') {
                indent = space;
            }

// If there is a replacer, it must be a function or an array.
// Otherwise, throw an error.

            rep = replacer;
            if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                    typeof replacer.length !== 'number')) {
                throw new Error('JSON.stringify');
            }

// Make a fake root object containing our value under the key of ''.
// Return the result of stringifying the value.

            return str('', {'': value});
        };
    }


// If the JSON object does not yet have a parse method, give it one.

    if (typeof JSON.parse !== 'function') {
        cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
        JSON.parse = function (text, reviver) {

// The parse method takes a text and an optional reviver function, and returns
// a JavaScript value if the text is a valid JSON text.

            var j;

            function walk(holder, key) {

// The walk method is used to recursively walk the resulting structure so
// that modifications can be made.

                var k, v, value = holder[key];
                if (value && typeof value === 'object') {
                    for (k in value) {
                        if (Object.prototype.hasOwnProperty.call(value, k)) {
                            v = walk(value, k);
                            if (v !== undefined) {
                                value[k] = v;
                            } else {
                                delete value[k];
                            }
                        }
                    }
                }
                return reviver.call(holder, key, value);
            }


// Parsing happens in four stages. In the first stage, we replace certain
// Unicode characters with escape sequences. JavaScript handles many characters
// incorrectly, either silently deleting them, or treating them as line endings.

            text = String(text);
            cx.lastIndex = 0;
            if (cx.test(text)) {
                text = text.replace(cx, function (a) {
                    return '\\u' +
                        ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                });
            }

// In the second stage, we run the text against regular expressions that look
// for non-JSON patterns. We are especially concerned with '()' and 'new'
// because they can cause invocation, and '=' because it can cause mutation.
// But just to be safe, we want to reject all unexpected forms.

// We split the second stage into 4 regexp operations in order to work around
// crippling inefficiencies in IE's and Safari's regexp engines. First we
// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
// replace all simple value tokens with ']' characters. Third, we delete all
// open brackets that follow a colon or comma or that begin the text. Finally,
// we look to see that the remaining characters are only whitespace or ']' or
// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

            if (/^[\],:{}\s]*$/
                    .test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
                        .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
                        .replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

// In the third stage we use the eval function to compile the text into a
// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
// in JavaScript: it can begin a block or an object literal. We wrap the text
// in parens to eliminate the ambiguity.

                j = eval('(' + text + ')');

// In the optional fourth stage, we recursively walk the new structure, passing
// each name/value pair to a reviver function for possible transformation.

                return typeof reviver === 'function'
                    ? walk({'': j}, '')
                    : j;
            }

// If the text is not JSON parseable, then a SyntaxError is thrown.

            throw new SyntaxError('JSON.parse');
        };
    }
}());


/*三*/


(function($) {
	if (typeof jQuery == 'undefined') {
    alert("need jquery");
} if(typeof Strophe == 'undefined'){
    alert("need Strophe");
} else {
if (typeof Easemob == 'undefined') {
    Easemob = {};
}
if (typeof Easemob.im == 'undefined') {
    Easemob.im = {};
    Easemob.im.version="1.0.7";
}
if (typeof Easemob.im.Connection !== 'undefined') {
    return;
}

var innerBase64 = (function() {
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    var obj = {
        /**
         * Encodes a string in base64
         *
         * @param {String}
         *            input The string to encode in base64.
         */
        encode : function(input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            do {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2)
                        + keyStr.charAt(enc3) + keyStr.charAt(enc4);
            } while (i < input.length);

            return output;
        },

        byteEncode : function(bytes) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            do {
                chr1 = bytes[i++];
                chr2 = bytes[i++];
                chr3 = bytes[i++];

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2)
                        + keyStr.charAt(enc3) + keyStr.charAt(enc4);
            } while (i < bytes.length);

            return output;
        },

        /**
         * Decodes a base64 string.
         *
         * @param {String}
         *            input The string to decode.
         */
        decode : function(input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            do {
                enc1 = keyStr.indexOf(input.charAt(i++));
                enc2 = keyStr.indexOf(input.charAt(i++));
                enc3 = keyStr.indexOf(input.charAt(i++));
                enc4 = keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }
            } while (i < input.length);

            return output;
        }
    };

    return obj;
})();

var emptyFn = function() {};
var progressFn = function(evt) {
};

var tempIndex = 0;
EASEMOB_IM_CONNCTION_USER_NOT_ASSIGN_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_OPEN_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_AUTH_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_OPEN_USERGRID_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_ATTACH_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_ATTACH_USERGRID_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_REOPEN_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_SERVER_CLOSE_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_SERVER_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_IQ_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_PING_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_NOTIFYVERSION_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_GETROSTER_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_CROSSDOMAIN_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_LISTENING_OUTOF_MAXRETRIES = tempIndex++;
EASEMOB_IM_CONNCTION_RECEIVEMSG_CONTENTERROR = tempIndex++;
EASEMOB_IM_CONNCTION_JOINROOM_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_GETROOM_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_GETROOMINFO_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_GETROOMMEMBER_ERROR = tempIndex++;
EASEMOB_IM_CONNCTION_GETROOMOCCUPANTS_ERROR = tempIndex++;

EASEMOB_IM_UPLOADFILE_BROWSER_ERROR = tempIndex++;
EASEMOB_IM_UPLOADFILE_ERROR = tempIndex++;
EASEMOB_IM_UPLOADFILE_NO_LOGIN = tempIndex++;
EASEMOB_IM_UPLOADFILE_NO_FILE = tempIndex++;
EASEMOB_IM_DOWNLOADFILE_ERROR = tempIndex++;
EASEMOB_IM_DOWNLOADFILE_NO_LOGIN = tempIndex++;
EASEMOB_IM_DOWNLOADFILE_BROWSER_ERROR = tempIndex++;

EASEMOB_IM_RESISTERUSER_ERROR = tempIndex++;

tempIndex = 0;
EASEMOB_IM_MESSAGE_REC_TEXT = tempIndex++;
EASEMOB_IM_MESSAGE_REC_TEXT_ERROR = tempIndex++;
EASEMOB_IM_MESSAGE_REC_EMOTION = tempIndex++;
EASEMOB_IM_MESSAGE_REC_PHOTO = tempIndex++;
EASEMOB_IM_MESSAGE_REC_AUDIO = tempIndex++;
EASEMOB_IM_MESSAGE_REC_AUDIO_FILE = tempIndex++;
EASEMOB_IM_MESSAGE_REC_VEDIO = tempIndex++;
EASEMOB_IM_MESSAGE_REC_VEDIO_FILE = tempIndex++;
EASEMOB_IM_MESSAGE_REC_FILE = tempIndex++;

EASEMOB_IM_MESSAGE_SED_TEXT = tempIndex++;
EASEMOB_IM_MESSAGE_SED_EMOTION = tempIndex++;
EASEMOB_IM_MESSAGE_SED_PHOTO = tempIndex++;
EASEMOB_IM_MESSAGE_SED_AUDIO = tempIndex++;
EASEMOB_IM_MESSAGE_SED_AUDIO_FILE = tempIndex++;
EASEMOB_IM_MESSAGE_SED_VEDIO = tempIndex++;
EASEMOB_IM_MESSAGE_SED_VEDIO_FILE = tempIndex++;
EASEMOB_IM_MESSAGE_SED_FILE = tempIndex++;
EASEMOB_IM_FILESIZE_LIMIT = 10485760;
var emotionPicData = {
        "[):]":"data:image/png;base64,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",
        "[:D]":"data:image/png;base64,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",
        "[;)]":"data:image/png;base64,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",
        "[:-o]":"data:image/png;base64,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",
        "[:p]":"data:image/png;base64,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",
        "[(H)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAGQUlEQVRYw8WXb2wT5x3HP7bPNs4f2wnECeFPEkhq0oaSho6u1bZQRBd1iA20ZaNS2/Bikyat6irejHdjIE3qtG55OzFpqaZqlcKqiNGp7RB1W1R1WuMRtrakUiAhsQ8S+3J24tjO+e7ZC5/ts53QoFbaIz3S4/Pd8/3+/v9+8H9etvv9IDyKD+gFDpqPeoFpQDV3qG+Qia+cQHiU7wEngWMbeF0FRoDhvkFmvhSB8Cj7gOGCxN5AG3WNW/HUb2ZTXSMOhwSGIJ2MkdOypBbvkIzPkkmphSuGgTN9gyTum0B4lF8CZ+ySi6b2HpraenA43SAEQggwDDAEwjBAWM6GQXpZYX72E5bUKKaJjq1lGts9wP8EnPQG2ti5tz8PDBbwPAFhAmMYJniJBMIglZjn9s1/YuiaCrzUN8irX0igAL6lrYdt3Y+X/iiAi3LAqrMwzGf537qWZXrqQ7KZJMDBvkHeK1xprwR/+RRDwMnmzr5ycACBBbykBVXN5s+ipJECOIbAbpNoa38Mt7seYCw8SlslAR/we2DxF79jpG8QvvP8NGOXpiukL0k2cT3O94fexdnyGk17LuDa/jqPDrzDxbciJRNY/MNhc7Bzx9ew2yW/GSFl69+AaG5uFocOHRK7d+8WBXm/aO/atUscP35cdHR0bOj9A3sl8ZffIMKj9AM4gCHgp0eOHOH8+fN0dHTg9/vRdR1ZlvM2X2edO3eOs2fPEgwG6erqwufzMTk5iWEY634TmTf46z8AaB//lFftZnLh9OnT2Gw2VldXyeVyuFwuPB4PAK/8+gBabAht4TkujPQDMDAwwIkTJwgEAtTW1uJ2uwkEAnR3dwPwwnPtpP/7NCvXB1i59m1WwoeJhL7Fy6c68dU5eO1NDgL7JOBgZ2cnU1NTGIZBJBJhfn6excVFstksAAk1W4zzif8oAPT09CDLMoqioCgKqVSKTCaD1+sFYOJGshQZpnP6a+387Efb+GavlyeeDwMMS4Cqqqp/fHwcIQTxeJxbt24RjUbJ5XKm/4miQ/m8TgDm5ua4ceMGTqeTRCKBLMvEYjHi8XjRafPbmh/ykfHw7hp+e6qRkYtKuwSEYrHYsatXr+JyuUgmk8iyTCJRypz7Hmooenb/15sAuHLlCq2trTgcDlKpFAsLC0QiEaLRKABHnwysnZyMfDT94HANhx5Xpm1APxCy2Wy43W40TUPX9TLwjy8/nWdvSvPUD0O8/9ECra2tBIPBouZmZ2dRVRVfvcRnb34DX63DQsLIh7J5ji/PoqQi+Uy4o4Wfz95huNJjfV4nl984zL4H/WXSqGqWp555n+ufVdcXX73EW+f383BXXTEdi7Jsmb8jnppDWTEJhEfp//gTQlcnO5m5vYQQ0P9EMy/+JIivXipLq8WLhODPF2a4eFlGTWogBEefDPDs0a1FyavBS2dlJYKSjpYIAKGuA99lU42/qrKVgd+r+KxTGavrhMgTyMghO0ChOKyuLJm5XlR5bj6ULFXQKH+vmKpFNanKOoEQZPUVAFWymO9aYmGm19vQSjat8en4XfNSa0iZQBTO+f/2BOtwSpRrweLxJVOY3wHp3DJAyEpgJBmbHRadj6HcTXHtozsb7hOdDsGeB+rW1ojVhCZ4MhvDEDrAmJXAmKFrw4t3b9K4ZTtOlx3czQSfvbgu8NyVX6FOXmL7tk1lzllKPtXgAEurcczmdabYD5jN44h8K4zdnqOzuwFtSWZ54o+0eJ1V25O+iTp5iUCTizqP3VR1pXNWgy+vqgX1n1mrITlj6JoamfoXvY9uoWGzm+kP/8DcB6/gJ8aOBjc7Gtykp/7Otdd/DMD+R7wVzlkRDRZwTc8yvzJdkP69NVuy8ChDwEhTSxCfv4u3/3abRSVbbXenjf2PeNnV5rHkhzXC1Vy6kSOy/DmreloFegvt+j17wsbNHTS3PMjU5wluz6TQVnUQ4PdL7HmgllqPvRrcknzWAMcEn9hwV1xT08jWlr04Jfc9i0sVEXOltSXk1BSG0DfeFVfNBXaJRv9O/N7tOHCsA24hZQIrmWjB4e5/LlhvMqrzbMHj9uKWanHaXUg2JxiCjLaMlsuQ1pZI55YK6v5yk1EFkX7gpQ3OhtPA2FcyG25gOq4cSu9rOv4f/iCz+2PhQ+cAAAAASUVORK5CYII=",
        "[:@]":"data:image/png;base64,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",
        "[:s]":"data:image/png;base64,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",
        "[:$]":"data:image/png;base64,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",
        "[:(]":"data:image/png;base64,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",
        "[:'(]":"data:image/png;base64,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",
        "[:|]":"data:image/png;base64,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",
        "[(a)]":"data:image/png;base64,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",
        "[8o|]":"data:image/png;base64,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",
        "[8-|]":"data:image/png;base64,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",
        "[+o(]":"data:image/png;base64,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",
        "[<o)]":"data:image/png;base64,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",
        "[|-)]":"data:image/png;base64,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",
        "[*-)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAFdElEQVRYw82XS2xc5RXHf/fO0/O6M3Ycx4kTOygPQmMyQQZCEimDYJGyYSQkJJBaGYmqZNddlxn2XXTbqgIvKkBiM1UXLVQIU6UphBCNycM0hMR2nBi/4nl4xnfm3vsdFhmP547HiZ1mwd3cuZ/+95zfOd+Z852r8X9cpfGTR4B4/TEfPXRubKs2tEdwOAykgCQCAlD/IQAiORFGgZH44PmxxwJQGj95CsgAKRHQvB40rxctGHDpnIqJsiykZtdhGBWRTNfRL794JIDS+ElDhBGQNLqOJxJGj0bQA/4HAiuzilVYxsqXENsByIow3D30VWHTAMWrJ/oFsghJTzyKNxFH8+hb2l9xHKpzS1QX8oiQQxjuef7C2EMB8peP9wM5TdfjtaDBd7kKlYIFwMCgwcCgsaHTcsHi6rkFl75vr4/lWzOI7eRFSPUevzi2IcDSt8cNkFFN15N2bBuffXAHq6pcmv1DCZIv9bR1/q/3b7XVH37BoHh9GrGdnEBq18lvCgDrcipKMqIkGejbwfjXRayqYupGiev/6+XlX/8JXyDC9xeXmJuqrAO4em5hQ/29BUXsQB+i60lRMrL6jgtg8dKxU0rJ73zdXdh4mbxSoHCvyqXz81z76j8szJq88GoGgInL7nqqmc5D9d5QkFBvF8qR9NTnR0+tA1BKMprHg7/LID9XBWDm9lqkoajB7idfBGjscaNuNqkP9Xbi6QiglMq4AOYuPNcvSlL+7oTLcCSxj1A0xivD77D3qUEAEj0HNizCzegju7ehHEnd/PTpI96m6NOaR8ffGXOJf/mr1/j9X37rLtTZ63TvDrUF2Iw+2BVD9/twzFpabyq+tDcWboji2+93uZu5v7uM/VB/jve4u+BW9X4jhFKS0psykPQ0tVZ/0EP/YYNyYYb//u0sNbPE7MRFvvnkD/W/Vqfb4EP1CRBBRBAlBGIhlCOpRh+Y/vczYjy5B38s5Krs0Q+nKNQLbPV69pXets1oI/3Q6R4GfhFDpHFiYS6Vmc1N0FwDrJ5tzVGl3tjD9xeXmJ+q4Avq7B/qZPueUMNQ/QYIPp/Gqdf77uunK/j8OvuPGnT3daAsu6FDBLEdlJK1Tjj5WVISh/sJGOGWzrTeEUIjnfc1m1hredcsVJi5Mt2SAaGtAZD6rWlNVqEeDUjZDuLIGoAoydeKlbgvHGhxVI//Ic63BCRQLa6glOSbMzBq3ltOh7ZFmyKvZ+QxATWvmSUTUZJzA+TLace00HWtvXPgxj/nmPxiEXvl/okX3RkgmPCx/XCEnc/E2oM3100doLxYQSnJNm9B1nbsP1bmi4S7Im2diwjF2ysN5wClu1VKd6vMX11m7nKJI2/2uGuG1sxBabGCbTmgaVnXPDCePfS+x+cd7jm4A03X2myFOxoAa8Vh/lqZ6/9YxDaF/acT7DkWfSDA1Pgcds0ZGRq++ZbXPQuQsUxrOH9niXivsQkAweOFHYMdhLu3c2u0QKTbi7Id17vNAEuzZWqmjaZpmbYT0eWPD55FyHTuihOOd2wAsPb7QZG2rhfzJnN3iiBknn974t0NZ8Kxjw58jkiqc6dBJB5s6g/rDUtrVpoBmtZXyhYz0yWUI7ljv5k4uurL23aaFUmLktGF2/mkU4sQSwTXO3pAXbSu5++ZLMxWEMhpGqlNjeWX/rrPEJEsQirQ4aOzO4Q/oG8JwLYcZn+ssFKxERjV0NLH35ksbOnL6OuRJ84iZESEYIeXSNRPOOxF17W2AI4SyssWy8sW5WV71UzmxJmpdx/50+zCe3v7RSQjwvBqPfj9HnR97QAVEZQSqu6RfKTufPKxfJx++ecBQ0TSIqQFSSIMtEgmgByQBbInzkwV+LlfPwH8Jg9a3haDYQAAAABJRU5ErkJggg==",
        "[:-#]":"data:image/png;base64,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",
        "[:-*]":"data:image/png;base64,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",
        "[^o)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAHHElEQVRYw+WXW2xcVxWGv3PmHM/NnvHEjhPHcTw0REmTJjZ1Y1KnbUyEioQqMYCqCqlIpkgphQcCDwjRh5qH5AGoGoSEVFQgogIhIoEB1TRQKhelShPX9dgJxbXjxHbieHybi2fGntvZi4czHo9jOylV39jSlvb1X/9ea+2114b/96L9L4tjQ+1+REJASIQg0AKCSHGBEBYII9ILdNe2Xkp8LARigw83idAFdK4I1F0VaLoOAivyVS6PyhVABAF+dupW9/CVpWogDnSdG4sP3pPAk7urm4CzwMlzY/HBhYEjLwBdAI5KD0aVF6PKg+ZwbEhWLIuhN27yy9MjROcL5VNxIHhuLL5GK/oGGC1ABxD+3Qt7BkSkS3e78OxuxN24ndsRYT6S3VD43NQSZ06G+fF33yc6X+DRLzTw4m8P0PZoFUA1cPJDmeCbR7d+ey6SPwPQ0l7Ncy8exuszmRhe5FTnOzTt8/H82SPr9p048neWkgVqd7h59tQh7m+rAWB5JkbPKyO0tleFvV69Y+dj7yXuSmC2r21g6ma+5RcvRZi/vYynyqD1+Db635xhKVng+V9/ugReXl7+wSBbGzx87qtBvD5zzVxmLsHi9WlAehuPDXxmUwIzl9peAjnpDdaT05z88eejnH91HABPlcHT39/PY6GdH+nKLc/GSYxNg0hX0/HwD9cRiLxzuBkIu7Zvwb199YSj/VEG/zVH0z4fex4MUL3NdVdB6USe8Su2lr1+k+BBf2lu8UaE9NQCQDD42cEJY40HK+nSK4w1wi+/Ns3EVRtsfCjB+FCCw5+vXwNaXqZGkvT1TJPPqtLYyLtROr6yiwqXg8rGraSnY6iC1QV8rXQLbr/d2iQiIVeZ8H9fmGfiagLTU4uz9jjDgzHyOYu+nmlmJ5dWgg+IgAi5jFUSvm1fiJzeSiKaJTGbpe+1aUQpNE2jsrEWUdI59vpBv756ekJoOq4aXwlw9N0ouulh/+On+fPLv2d4MMaVvgXbLH1RxFKIKlZLMXp5gXxWUb//S/gbjtFz9hwXzk+zlMpz+1qKdDSLWAp3jQ+lQCnpLBFQSkKmz2uDWorZ8RT5rCLQ8BDeqi1k0ikAJsdSJUCxLKRgIYXinpu2Vpqav0hmMQ5APq8Y+49twqnRJGJZaICr2oso6SjTgHQYHmeRgIVYUnSietxuN+1PfLksfHntPUXB9noLBCpr7sMfqKP5aAe79h4AIBHN2WSWLShYYFkYbhOlaDHKCKAbDqRgFfu2ExmGgdPp5MSpn/LEM9/CFwgw9OZpZif6baErfoDtC5nkDG63G9M0+dFf3mK47yJuT5aL3d9DlCCWjVvhdSFKgjrAjTeajykRW53FE3k89g2NR65imiamaXLfgUMEttYQi3yAp9IBBVU6kVgWpqlRyKVZik1gGAa6rnPgyCNEpwcA8AccRZPZ60WJ/RaIkmItqrOgcHt0ausriE4NMjX8DxwOB7qu03/+J+SzKXbtcRdVb5uBgkX9LicA4X+eoZBLo2ka0cgHXA//FaNCo6bOKK0XS6GU2IHo+vlDfiUS3xKso8LrLGl1fjrH23+LAlDX1Eo+kyQ2M4Jhajz+ZC1mhbaq/mK58HqMhZk8Xn893uodzE70A/Cp9koad7tKJsskM8yMzKxGwtGeB8S/I4C72luyJ8D0ZJYrfSmW07btaraZPPCQF3/AuPMhBoF8Thi4mCRyK2/7kKmx96Cb3ftcNtci4cW5JLFbsXi5E4ZzS7kWV+XaMLu9wWT7jgCJWAHT1PBU6nbssRRrPdBuGg44/Egl+ZwiEbOorTOKeYJag5tNZVFKwsZqHKB3ObHUUlVbeYda7Y7Pp60HErlj1WrDcAg1tbp9mzbAS8eWECXd5XHgbCFbYDm+VIps5U62OlY2VzbGSlX2rVjpr91r4yXn0lh5hQirBO4PvT+oFL2phXQZUBnIBkAbE1N3IWa3o5FFlEjvg09fW/8aZlLZ3uR8isqAe52Jy51t3ZTIBgniugaxmTS5TIGVPHNdQjL0h71/0jUttHWnD9NplO2XTcDLOrIR39WRXKbAzWtRgDNtz9z4DoCxnrV0WpYKz0zGg3UNRRJ3nG64J0HkambThKTlqWqqGyvWEMtmCkxNJBAhrBVPv2FWfOipkYQIIaug4pGbCTKpLMW3s2RXZ5W+qXCHU1uNqMX1yUSGWzfiWHkVF5FQ29dvJO75MXnv1U82C9KNEKzyO6kOuNB1bUMbyCbmUEqIzi8Tj2YAwhpa6MiJ8YkP/TPq/81uvyg5KxDSNY3KKhOf34lhaJs4nC08m7VYTORILuaw7Ge9W4POh5+dSHykr9nlX33iGMIZgRZEMAydigqdCqejzPGEbNYim7Eo5NWKHno16Gr/xuRbH8vn9NIrwWYROkWkA2iRsjygTAFhoBc4e/S5ycF7Yf4XB/xJUbaYD5UAAAAASUVORK5CYII=",
        "[8-)]":"data:image/png;base64,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",
        "[(|)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAWlBMVEUAAADuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjvXj/ziG3zgWbxc1buUC/0ln34uaTuSSj2pIz2q5TuSSj1nYX3spzxbE70j3XvVzfyel7cxRSSAAAAHnRSTlMAL3+fv18PT6//799v/////////x///8////////+sIC8ZAAAA9ElEQVQ4y61TQYKDIAxMEcSktWrDttV2///NJaJoK3DauSUzJGQIAP+Ek6q0MvUaWtVoZXe0xhmkQnhewsvCG8L22vWSq+Q4RlThAA037pbUBRxtApxLavzh+5ohp3EPB+CwZ77GzOOD9xXBYMexwwEaQIngWRT4FmNBYHEYmaeMoJFLSo9XRiBeNXOJd5Incd8785srEbw3iE/mNn3FReGbJCZ9xOc12CYG2XhR9DwOeT4obgVeDJ3GqcADVNjeNzPO9XEvvWHx1emUWNx6two2vdr0aeAR5svA1DXCXmYFNa1PnIMtNgh/oNBA4LITxEm+4j9JsxdaKeGywAAAAABJRU5ErkJggg==",
        "[(u)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAtFBMVEUAAADuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjuSSjmvbvuSSjvVzf0j3XxbE7pinrmvbvzgWb4uaT1nYXrbVXmvbvmvbvwZUftUDHsWT3uSSj0ln3uUC/qg3HsZkzmvbvziG3yel7rdF/mvbvuSSjnrqjmvbvmvbvmvbv2q5TtVzrvXj/np5/mvbvxc1booJbmvbvmvbvooJb2pIzqe2juSSjpkYPmtbHqg3Ert3r1AAAAPHRSTlMADz8fX39vT8//3++PD7//////j///////r///b6//////3////78v/+9fz/////8v//9/n7///5///1+U7zcrAAABPUlEQVQ4y81T21LCMBDtLaXb2FIuwRWFAgpFpUVEpej//5dJk95oRsc3z0Nmzp4zu9nNxjD+L0zLspvcIYS4daTnAYfvVJxCgatAcgIKoal4P4oGIjAcCYsDFUKhWzBmE8mv8YaXp7UBRJVwwNhU0lvEuzBs6DAzDBvmjMWSLhCX0IaoMKkyDHE10xjuGXuQbI0bp2NIYMrKS8L20fBaumiDihr9so2npNkEuMUYeIpIBdaroOnwxaRMH6IqBSyeA1IbkmKSCY13bK5CKW7s1pikI6s6hT2+0AtdOA5sp6KvePQudekYl4a3ri4dssgW3zU63wnIPuLikngqdqOzZA5kBy6cEFP5ih3kqsKZn56pW1Rx+aVMkGg32aaih7N6AR1cnuAzVZuoRf6Fe57A/uHDHEc5kF/+lPe3P/gNbKkfpYJqqEsAAAAASUVORK5CYII=",
        "[(S)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACK1BMVEUAAABjZIRjZIRjZIRjZIRjZIRjZIRrbIt7fJpjZIRjZISDhKGrrMbLzOTj5fujpL9jZIRjZISLjKnT1Ozb3PNjZIRjZITDxN3S1Ozi5Puio7/g4vvf4fpyc5JjZIRzdJLh4/vd3/rNzutjZISztM7c3vra3Pq7vdza3PPZ2/rX2fq5u9yCg6He4PrV1/nU1vm2uNugob+nqs64u+C0tttwcZKJi6+Wl758faFjZISeoL/W2PnS1Pm/weZwdJiBharMzvjKzPjIyvjHyfjFx/ixs+Joaoudnr/R0/nP0fm2ueDJy/jDxffCxPfAwvenqdqbnb7T1fnNz/i5vOaFibDExve/wfe9v/e8vvaam77O0Pifos60tuW8vve6vPazte59fqHLzfjBw/e4uva3ufaCg65ub5nDxPDGyPiho9Oho9l3eKBmZ4qEhsKanMWxs+hoaYuIibWvsO6jpeBnaItxcp+Zm+x4ea1vcJK+wPe5u/anqeBqa5GGiMmbnfOTleWQkr22uPa0tvatr+6Aga5jZISDhLyCg7yBgruEhcKSk96anPN0daapq+CytPWxs/WvsfWtr/WipOaBg7V0daBzdaBzdKCeoO2govSeoPOdn/OFh8mlp+CytPausPWsrvWqrPWoqvSnqfSlp/SkpvSipPSfofOMjtdnaYuho9+pq/ScnvOLjcOmqPSWmOx7fLSUldiho/Rpa5Fqa5J9frSKjNCPkd6IitB3ea0J8x3fAAAAuXRSTlMALz8fb7/////vn///////z3////9PD/////////9f/////9////////////////////////////+P////////////////////////////////////////////////////////////////////////////////////////////////////r/////////////////////////////////////////////////////////////////////CFNf8AAAIBSURBVDjLY2AgDTAyMTEx45JkZmFlY2PnYGPjxC7NxcbNw8vHx88uwIJNXpBNSJiPj09EiFMUm7QYK7s4HwgICYhhlRfgEAHL83NilWfggspLsGM1n4GFnRckLSkpxYpVXoyTBywtLSMrh1WBoDxIWkFGUYkNuwuUVfhA0qpq6srYQ5dNAyytqaWN3QksOjK6IGk9fQNlQUFBzJjgMoRIGxmbmJqZW7BZoitgtdLUsta3sbWzd3B0cnZxdUNXweoOlPbw9LKz83Z28fH180f2i4AgUEFAoIdnkEOwXYhLqK9fWHgEGxNcnokNqMAy0jPKwdHbOToGJB0bF8/GCA9CZTZg0LEkOCQCDQ/1TQJJJ6ekCiDcnwYyjZEt3TkDJJ0Jks7K5mSEp4GcXHDiUs4L9QHqzmcrKMwqKi5RhkSImCBnaVk5FzikKpLCKmPjqqoL2EBAQI6F1ZKLla2mtq4e4l4x5QagdEpjU3MLW6scEwtrSVt7R2dXXXd3DzTsRdl6gdJ9/RMmTpo8Zeq0tukzZs4CSnfP5oSFu2XBnLn9E+bNX7Bw0eIlS2csA0t3L2dDJA+uFSuB0qug0qtB0t2zc5CTD1fJmlWL1iJJr1vPhpq85Dinbli6ESbdPXuTMhN6yuTi3Lxlaz1Qctv2HTnKgtgSr1yrMiQkuERxZ28xdJMBvF2JRlNcrBgAAAAASUVORK5CYII=",
        "[(*)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABwlBMVEUAAAC6iS+6iS+6iS+6iS+6iS+6iS+6iS+6iS+9jzHVuUG6iS+6iS/Rsj/18Ve6iS/p3E/5+FrZwES6iS/BljTdx0fx6lS6iS/491nRsT7l1Uz381bv4U7IoDbFnTf591n49Vj38FT27FH0503z4kry3kbx2kPw1kDv0j3uzjrtyjfsxjTMnzD27lP16k/05Uzz4Ejy3EXx2ELw1D/v0DzuzDntyDbsxDPMnjD38VX16E7z40rrwzHMni/39Fb271Pz4UjtyTbsxTPrwTDMnS66iS/t41H49lj27VHy30fuzzrtyzfsxzTqvy7MnC7s4E/161D05kzz4Uny3UXw1T/v0TzuzTnqvS3Roi3q10j05Evx20Tw10Hv0z7uzzvtyzjsxzXrwzLqvy/pvCzXpyy9jjDnzUHx2ULrwjDqvi3puyvdrCu6iS/pzT7syDXrxDLqwC/qvCzpuSrotyjiryfKoDPv0T3rwjHqvi7ouCnotSfnsyXnsSTKmCrhvTjtzDjSoy7Lmy3isSnntCbnsiTbpybHmTHYqy/Ajy68iy7QnSnkriS/ji3ctDPeszDDky/WoijAkC/lvDHGly/FkyzeqSYNOZEtAAAAlnRSTlMADy9vv9//P5///x/v//9f////z////3////////////////////////////////////////////////////////////9P//////////////////////////////////////////////////+P//////////////////////////////////////////////////////+XLHICAAABkklEQVQ4y5VT5V8CURCU42455AGeBSaI3Z2gWKjY3d3d7WEXigrG/+sDQY4Dj5/7cXdmbt7ebEjIf0tEBAGIySAACiSCcwkALQigpaEgE7KI5AqpkIQSwphwIYmISIZRCLggIIphGPnfEjQoMCDaR0Km4hSS4zmjjgFvC68OOBXrnMfFJ3g7LueJTmF3abRJuuSU1LT0jMys7Jyfj3ER6rjcvPyCwqLiktKy8gqPmV+ERlupq9K76YZqr1k3Ql1jzMuvddHr6hsauY8xocgmTXNlS5XejOmtbe2Gjk7ffRFI2mXs7untK+ofGMT0oWH+PjFiZNQ8Nj4xOTU9Mzs3779vAi0sLrnoyyura4H+xzpsYPrm1vbO7t5+AIAIHRzixx0dr56cnrEWJOIDVHB+gemXV9c3tyx7B0q/tN5j+sMjgPXpmWVZG+WXpZfy1zegTDISbHaWtfPTTTnePwC5dCUUfH49W2megAOQymNMjMBi8c0Vie+B0xDROAoqn3sheTkkSEDciwxwbyZKGezSnYf+Da0FSRY6a5opAAAAAElFTkSuQmCC",
        "[(#)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAtFBMVEUAAADtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCG6iS+6iS/BeyzGcivJbSrMaSq6iS/RqirgwCboyyXDeCztMCHNZynFmSzw1iP44iK6iS/03CLTXCi6iS+6iS/s0SS9gy7TXCjOZSnNpCrBfC3ZtSjPYinVrym6iS/BlC3NZym6iS/kxibQYSi9ji7ZUSbcuyfdSyXYUya6iS/PYynVWSftMCFeFPtwAAAAPHRSTlMAP28fX78v308P/6+fj+8fb8///9//////r3//////f///D+///x+f/2//3/9P/3/f/4/////Pny+/78+blAboAAABIElEQVQ4y7VTa1ODMBAEm+cBaX20aEqxKCpirdi0WuX//y+xQKAhM44zep+S281eNrlznN+G+/+EE2t2hPQSd1nSU0OaQYkuxvoaqJHmgAZnmtoeP6TBr/eBb97DE8gZT07PzifjSp5SbhK4gIvpLLwMZ9MrCmCxK+fRIv6ORTQHbPF6HS3jOpZRwob4zW0at5He3Q9w/hDFXWSPxw/pSgxJ3iPkicd4C7pIBhhMAgBQX7LODHpa9QirhErT6PO6R1i/WGwWocZfC9ufb1TrM1UbC87FVu0O+E5tBbe8NMBbobI8U8W7ADnAicDVtfcfyee+UguAmAXKumNY0w9uaRQJSNtRzTdx/4ghtWI50j3GbbjjdVlmnQD50+CwP5/NL6lmHHJczhNXAAAAAElFTkSuQmCC",
        "[(R)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABO1BMVEUAAADtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtMCHtOx/ydRj2rxH50Qz16wnw7gj76Qr3ug/zgBbuRx7tMCHtMCH0jBX33gvn8QbG8Qie7AyC4xKL5hC67wri9Ab1oxLtMCHwXhvl8waY6A9b1ho1ySJIzx7V8gfy7AgxyS4lyV8byYQhyWsuyTo+zCCh6w3q8AcVyZwFydkCyuYOybUoyVNu3BYYyZD0mBTE8ggFt9kTdq4ZWpshNYMbUJUVbKgJpM0LycERf7QDwN8RyajtMCEfPonxaRkuMoZCLotQK45XKpBJLIw1MIcIyc0NksD46glXKpBXKpBXKpA8L4kdR49l2RhXKpBTKo9XKpBXKpDw7giC4xI1ySIbyYQCyuYhNYNXKpBXKpAuMoYJpM3l8wb0jBX41gnuAAAAaXRSTlMAT4+/7/+fXx+vP3//////////////D8//////////////L////////////////////////////////////////////////9///////////////28/3////x//D6+/v7+/v7+/f7+/v79O3N4bAAABdklEQVQ4y+2Q51bCQBBGF5MJqIEQGx0LZRQ0NBcLZbGiYuy9IcH2/k/gEIpHj2+g99ecvTPf7C5jfw7HkCQDgKQ4Xb9Yl9KRfYZHfvpRANXt0by6ro+NT0xSzreUKR+o/kAwFI4Q0ekZ7+wkyI4vPydDTIsnkthjPrLg9QM4B/MyuFPTpNOLS4SRQczm8rPqoMNHPpfFTGGZdymuIK4Gx0Ge690vlsohGn3dYS2Nq/l18NnvA1UrZdGg43KlKkRtY5PKrTRGUzF7iQL+eNL226JHdYfzXcyWNKhTg6wGEpih/A0xYK/MuUFL9sHBHODOJ7HQnT9oHJpHx1TUOD9BDHlAYUPgCWF6mZfp+Ni0aVB52omIntEOCbQwLnJeoXnTPGfs4tK8EuKa8wLe6DFgdfBGcIlzun+j4xm7Ne8oosyLmIzvAwPQ7YY9IQ7Ne/tjHswDIeitiHE/MEl6fGo+t1qWZbXb3Z99ab9a1lur1Wy+f0jsH5tPJc9K5e3l4mIAAAAASUVORK5CYII=",
        "[({)]":"data:image/png;base64,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",
        "[(})]":"data:image/png;base64,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",
        "[(k)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACqVBMVEUAAADuSSjuSSjjPB7hOh3gORzuSSjuSSjuSSjuSSjhOh3fORvdNhrcNBjuSSjtSCfrRibqRCTgORzfOBvdNhnbMxjZMRbXLxTuSSjvVzfrRSXpQyPnQSLlPyDgORzcNRnaMxfYMRbWLhTULBLTKhHvXT/1qZPxmIHsUjPqRSXoQyPmQCHkPiDiPB7gORzeNxrYMBXWLhPULBLSKRDQJw7OJQ3uSSjwb1LzoYvthW3oaE7pSCnoQiPkPh/iOx7gORzcNBjaMhfVLRPTKxHRKRDPJg7NJAzLIgrJIAnuSSjxgGbvjnfpcljmVjroRifoQiLmPyHjPR/hOx3dNhrbNBjZMhbXLxTRKA/PJg3LIQrJHwjHHQbuVDXwhWzsemHqWz7qSCnpRCTnQSLlPyDhOh3ZMRbVLBLTKhHMIwvKIQrIHwjGHAbEGgTCGAPuUTHvZUjub1PtWDrjPB7hOhzSKhDQJw/OJQ3KIAnIHgjCFwLAFQG/FADuSSjuTy/vaEruVjbsRyfoTC/qblbpbFXna1TmaVLkZ1HjZlDhZE/gYk3eYEzcX0rbXUnZW0jYWUfWWEXVVkTJKhXDGQTBFwK/FQC/FADuSSjvXj/2r5rynIXuiXHrY0foa1TzvrL8+vb47OfcfW/DGQPBFgL0lXzqa1HjZE7zwbj78e7rr6bIKBPDGAPBFgHuZknuhGzqdl3mRynlf2/76Ob329jWXEzCGAO/FAC/FADrRibpRCTnRSfnWT3mXkTiRCfYSzb42db75uTxwLrMNSG/FADlPyDjPB7hPiHhRireOh7QJw7OJQzURzP32dbJKBPeNxvcNRnLIgrJIAnXXEzMNCG/FADYMBXVLRPHHQbFGgW/FADRKA/PJg3NIwzKIQnEGQS/FADGHAbBFgK/FAC/FABbs/P1AAAA43RSTlMALz8PPw+f/68Pr//PD2//zw9f////zw9f////7y9//////88P////////////z///////zw+P/////////////////////88Pv///////////////////////n9//////////////////////n5////////////////+ff+/////////////////////////////////P////////////////////////////////////////718Pf+///////////y8Pf+//////////D3//////zx+///8Pb+8fv/+Pfy/fPzM30SoAAAGnSURBVDjLY2AYfoCRCcpgZmHFJs/Gzs7BCWZxcfPwYsrzsbOz8wsIAllCwiKiYuLo8hKSkuz8UtIysgxywvIKikrKKmjyqmrqGppa2jq6evryCgaGRsYmpsjyZuYWllbWNtq2dvb6Do4GTs4urm7uHnBpTk8vbx9fP/+AwCDu4JDQMCfn8Ai3yKhomDxTTGxcfEJiUnJgCtB5qWFp6eERGZlZ2Tm5EO15+QWFQOclJRcVg51nVFJallFekZ1TWVUNlK6pravnb9DUamxqbmlta+/o7Oru6e3rnzBx0uQpU6cyMEybPmPmrNmaQOfNmTsPAeYvWLho6lSQAnbJxTNnLYE4b+my5VCwYuWq1VMhCpg4+NesXbce6rwNG0Fg0+acLVUg6a3bQI7cvmPnrt17oM7bu2///gMHgc4Dyx+CevPwkaPHjoND78TJU6cPnAE7D0keCM6eg4be+QsXL0GcN/XyIZS4uHIVEnrXrkOdd/kGenTevAUOPYjzplbfwEwQt+9UZN+FOG/qPexp7j7UeVO34UqVDx6CpB8ewpNwDz169GiQ5SUAHrG6xm0Tgr0AAAAASUVORK5CYII=",
        "[(F)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACT1BMVEUAAAD3Uz70Pyf2SDH8dGf4W0j0QSjwJwnhGgXOEgu9DBH3Uz70PSTuIwXoOyv1V0XNEgu7CxK2CRS4ChOtBRfcGAfKFBDpNSLIEwbPFAjHEA64ChOtBRf3VkLEFBbmHwS+EwLWFgj4V0OzCBXjJxS9FgPgGgO9Fh74Wke3DBbLIxLdHgTlHAPTTE/7bF3MIBrHHAjwJgjjbGz7b2DIHAnsNBvsfXurBBf7cmXNFAfYKRPzOiDWLin1lZH8dmrTGAHxRzH/rqj/iH/hGgPfKxL2TzrylJHBRFL/iH/kHAHtRjG+Pk6gABz/iH/oHgD3X03LHBrjmp2pBBigABzwJAXwOB/5ZFPZHxL1p6PVanD/iH/8dmn0STPpKRLycWjldHG4ChOtBRZYngB3kRPkYT3zNxz5YU/IEA28CxFUlABmhQN6fhGPgSi4bzPLWivIShjFOQazNQKMQATNEgxWmQBVlQBSkABQjABOhwBMggBJfQBHeABFcwBDbgBBaQA+ZAA8YQBYngBXmgBVlgBAgAFGgQBJfQBIeQBEcgBDbgBBaQA+ZQBYngBYnAAPXgdLgQBJfQBHeABGdQBDcABBagBQjABNhQBKfgBHdwBDcABAaAA9YgBXmgBUlABRjQBNhgBAaAA+ZAAPXgdXmwBUlQBHdwBDcABXnABTkgBOhwBKfwBJkAFUlQBRjQBNhgBKfwBTkQBIegBEcAAPXgcPXgdWmQBIegBDbwAlYQMPXgdRjwBNhgA0YwEPXgdMggBIegBEcAAPXgcPXgcPXgcPXgeUFVsMAAAAxXRSTlMAP18vf9////+/T3////////+fPx//////////f6//////v///////////////L/////9v////v6////////////8//////99P////D3///////x////////8P7//////vL1/v////3y+P//////////+/D0//3////6/v////308P309//88vHz8/L28P/x8/Px8/H0+v/////48Pr///zw+/n//vf6+PXw+fv39/T//vT39PH///z49f79/PDz8vH2/vD/nTwCsAAAFzSURBVDjLY2AgATAyMbOwsrFzcHLhUMDNw8vHD1QgICgkLIJDgaiYODuHhKSUtAw2BbJABVJy7PIKijgUKIEUKKuoqilKqWNVoAFSoKmlraMopYtNgR5Ygb6BIVCBETYFxmAFJqYgBWbmWBRYgBRYWlkDFdjYYlNgB1Jg7wBW4IhFgRNIgbOLK0iBlJs7pgIPkAJPL4gCdW8fDAW+IAV+/mAFAYFBwVhNCAkNAykIj4jE4oaoaKCCGJCC2Lj4hERMBUnJKTypaWATFNMzsEZ3ZlZ2Tm5efkFhUTFW+ZLSsvKKyqrqmtq6+gYs8o1NzQwtrZVt7R2dXd3YDOjpBRJ9/RMmMjBMmjwFV7LsY2CYOm36jJmzZuNSMGfuvPlABQsW4lCwaPESoIKly7DJLV+xchXDotVr1q5bj13z4g3zN27avAV3ttm6Yf627Tt24slYu3Zv275nL76st2//AYa+gwTy56LNBBQcOnwEv4Ijh9GMAAAtMGlRHWiirgAAAABJRU5ErkJggg==",
        "[(W)]":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACLlBMVEUAAABZnwBVlwBQiwBKfwBGdgBVlgBQigBKfgBEcQBAaABTlABEcQA+ZQAPXgcPXgcPXgdPiABJfQA4bQEbYAUPXgcPXgcPXgdOiABMgwBLgAAPXgcPXgcZXgUUXgYPXgdYnQBWmABTkwBRjgBOhwBMggBKfQBCdgAyZAE+YwAPXgcPXgdYnQBVmABxggWhWwXDOAHQIgWETwQ3XAUxaAJAaAA/ZQD3VD/6iHr2blvpHQLcGAbPEwvCDg96Kg5PYAJAaQAPXgf4XUv7cmT6hnnxKQu1CRSoAxiaKgpeTQQPXgcPXgf5YE70Qim/DBDMEQy5ChKsBRf7bFz8k4j3V0L0PCK5ChPaFweoAxj7bFz9q6T6k4bSFAreGQanAxn7bl/9sar5iHrJEhDuIwWpBBj7b2H+vbjnLhmrBRdJjwFTkgBPigD8eGv8lIn2TjjcJRjzOyG5ChKuBhcTYgZOkwBTkgBPigBLgQBIegD+fnP9fnL5ZFPhIhD1Tzu9CxEtdwRTkgBLgQBHeQBEcQD+f3X8dWfrIwn2XUz3VEDNEgzADRBSkABPiQBDcABAaQD+gnj8cmTxKw36aFj7bl/REwpLgABHeAA/aAA9YQD+g3nyNRrxLA/7b2HfGQXpST7dJxrlGwPsHwL2TDfjGwTMEgzRFArZFwfhGgXqIwnvIwXlHATJEA3OEgvVFQndGAboKRP2UDvwJQcPXgfQEwvWFQndGAb2TjjzOR/xKQsPXgdPdr5LAAAAunRSTlMAP//ffw/f///fL0//z4+/fz+vz///zw8vPw8v78+Pry8/Pz8fz/+/r69PP3/v////r///798Pn///////////Px+f////////v59f//////9PH///////zz///////3//////v6///29PPz/v/////+8P/////+9vL/////9/b+///59v/////98PL8//n7//////X1/P/1/v////32//////X5//////7w9f3/////+fbx8/Lx+fT98nuUF/AAABm0lEQVQ4y2NgIB4wMjGzsOJVwcbOwcmFTwE3OwcPLx8/vwBOFYJCwiKiQCAmjk1WQlKKQVpGVFaOX1RUHl1SQVFJWUVVTV1GRkOTgUFLRlQbTYGOrp6+gaGRsaiJqRmQKyDKj26EuYWllbWNrZ29A4gnLiPqiCrv5OziClLg5u7hCeJ7iXqjKvBx9oUo8PMPCATypUW9UOSDgkNCwQrCwv0DIkAiMqiOiIyKhiiIifUPiAOJyIuiKIhPSAQrSEoGKkhhAPsDRUFqGsSKdJCCDJCIt2hmVjZCQU5uHkhBfgFQQWERSES7uKS0rLwCKl9ZVQ1SUFMLUlAHFtIWrW8oa2xqhihoafUBKmhr7wAq6OyCKtDu7mls6u0D8/onABVMnDQZpGAKA0wBA8PUab3TZ4B4M4EKZs2eA1IwlwFJAQLMm1+zYOEikILF2BUwLFm6bPmKSR0FK1fhUMCwes3ades3FGyE8TdhpAiGzVu2btu+A8YTEBXHnwXQ4gITiBFSILoTv7w2ZqJEBZsIKZAXFcCvQIaAAm1RRDgBAONoa547W9uXAAAAAElFTkSuQmCC",
        "[(D)]":"data:image/gif;base64,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"
};
var createStandardXHR = function () {
    try {
        return new window.XMLHttpRequest();
    } catch( e ) {
        return false;
    }
};
var createActiveXHR = function () {
    try {
        return new window.ActiveXObject( "Microsoft.XMLHTTP" );
    } catch( e ) {
        return false;
    }
};
if (window.XDomainRequest) {
    XDomainRequest.prototype.oldsend = XDomainRequest.prototype.send;
    XDomainRequest.prototype.send = function() {
        XDomainRequest.prototype.oldsend.apply(this, arguments);
        this.readyState = 2;
    };
}

var xmlrequest = function (crossDomain){
    crossDomain = crossDomain || true;
    var temp = createStandardXHR () || createActiveXHR();
    temp.open("GET","AjaxServlet",true);
    if ("withCredentials" in temp) {
        return temp;
    }
    if(!crossDomain){
        return temp;
    }
    if(window.XDomainRequest===undefined){
        return temp;
    }
    var xhr = new XDomainRequest();
    xhr.readyState = 0;
    xhr.status = 100;
    xhr.onreadystatechange = emptyFn;
    xhr.onload = function () {
        xhr.readyState = 4;
        xhr.status = 200;

        var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
        xmlDoc.async = "false";
        xmlDoc.loadXML(xhr.responseText);
        xhr.responseXML = xmlDoc;
        xhr.response = xhr.responseText;
        xhr.onreadystatechange();
    };
    xhr.ontimeout = xhr.onerror = function(){
        xhr.readyState = 4;
        xhr.status = 500;
        xhr.onreadystatechange();
    };
    return xhr;
};
Strophe.Request.prototype._newXHR = function(){
    var xhr =  xmlrequest(true);
  if (xhr.overrideMimeType) {
      xhr.overrideMimeType("text/xml");
  }
  xhr.onreadystatechange = this.func.bind(null, this);
  return xhr;
};

function getIEVersion(){
    var ua = navigator.userAgent,matches,tridentMap={'4':8,'5':9,'6':10,'7':11};
    matches = ua.match(/MSIE (\d+)/i);
    if(matches&&matches[1])
    {
        return +matches[1];
    }
    matches = ua.match(/Trident\/(\d+)/i);
    if(matches&&matches[1])
    {
        return tridentMap[matches[1]]||null;
    }
    return null;
};

var tepmxhr = xmlrequest();
var hasSetRequestHeader = (tepmxhr.setRequestHeader || false );
var hasOverrideMimeType = (tepmxhr.overrideMimeType || false);
tepmxhr = null;

var doAjaxRequest = function(options) {
    var dataType = options.dataType || 'text';
    var suc = options.success || emptyFn;
    var error = options.error || emptyFn;
    var xhr = xmlrequest();
    xhr.onreadystatechange = function (){
        if( xhr.readyState === 4){
            var status = xhr.status || 0;
            if (status == 200) {
                if(dataType=='text'){
                    suc(xhr.responseText,xhr);
                    return;
                }
                if(dataType=='json'){
                    try{
                        var json = $.parseJSON(xhr.responseText);
                        suc(json,xhr);
                    } catch(e){
                        error(xhr.responseText,xhr,"错误的数据,无法转换为json");
                    }
                    return;
                }
                if(dataType=='xml'){
                    if (xhr.responseXML && xhr.responseXML.documentElement) {
                        suc(xhr.responseXML.documentElement,xhr);
                    } else {
                        error(xhr.responseText,xhr,"浏览器不支持ajax返回xml对象");
                    }
                    return;
                }
                suc(xhr.response || xhr.responseText,xhr);
                return;
            } else {
                if(dataType=='json'){
                    try{
                        var json = $.parseJSON(xhr.responseText);
                        error(json,xhr,"服务器返回错误信息");
                    } catch(e){
                        error(xhr.responseText,xhr,"服务器返回错误信息");
                    }
                    return;
                }
                if(dataType=='xml'){
                    if (xhr.responseXML && xhr.responseXML.documentElement) {
                        error(xhr.responseXML.documentElement,xhr,"服务器返回错误信息");
                    } else {
                        error(xhr.responseText,xhr,"服务器返回错误信息");
                    }
                    return;
                }
                error(xhr.responseText,xhr,"服务器返回错误信息");
                return;
            }
        }
        if( xhr.readyState === 0){
            error(xhr.responseText,xhr,"服务器异常");
        }
    };

    if(options.responseType){
        if(xhr.responseType){
            xhr.responseType = options.responseType;
        } else {
            error('',xhr,"当前浏览器不支持设置响应类型");
            return null;
        }
    }
    if(options.mimeType){
        if(hasOverrideMimeType){
            xhr.overrideMimeType(options.mimeType);
        } else {
            error('',xhr,"当前浏览器不支持设置mimeType");
            return null;
        }
    }

    var type = options.type || "POST";
    xhr.open(type, options.url);

    var headers = options.headers || {};
    for(var key in headers){
        if(hasSetRequestHeader){
            xhr.setRequestHeader(key, headers[key]);
        } else {
            error('',xhr,"当前浏览器不支持设置header");
            return null;
        }
    }

    var data = options.data || null;
    xhr.send(data);
    return xhr;
};

var registerUserFn = function(options){
    var orgName = options.orgName || '';
    var appName = options.appName || '';
    var appKey = options.appKey || '';
    if(!orgName && !appName && appKey){
        var devInfos = appKey.split('#');
        if(devInfos.length==2){
            orgName = devInfos[0];
            appName = devInfos[1];
        }
    }
    if(!orgName && !appName){
        options.error({
            type : EASEMOB_IM_RESISTERUSER_ERROR,
            msg : '没有指定开发者信息'
        });
        return;
    }

    var url = options.https ? 'https://a1.easemob.com' : 'http://a1.easemob.com';
    var apiUrl = options.apiUrl || url;
    var restUrl = apiUrl + '/' + orgName + '/' + appName + '/users';

    var userjson = {
            username : options.username,
            password : options.password,
            nickname : options.nickname || ''
    };

    var userinfo = JSON.stringify(userjson);
    var options = {
        url : restUrl,
        dataType : 'json',
        data : userinfo,
        success : options.success || emptyFn,
        error : options.error || emptyFn
    };
    var param = doAjaxRequest(options);
    return param;
};


var getFileUrlFn = function(fileInputId) {
    var uri = {
        url : '',
        filename : '',
        filetype : ''
    };

    if(!isCanUploadFileAsync) return uri;
    var wu = window.URL || window.webkitURL || window.mozURL || window.msURL;
    if (wu && wu.createObjectURL) {
        var fileItems = document.getElementById(fileInputId).files;
        if (fileItems.length > 0) {
            var u = fileItems.item(0);
            uri.url = wu.createObjectURL(u);
            uri.filename = u.name || '';
        }
    } else { // IE
        var u = document.getElementById(fileInputId).value;
        uri.url = u;
        var pos1 = u.lastIndexOf('/');
        var pos2 = u.lastIndexOf('\\');
        var pos = Math.max(pos1, pos2)
        if (pos < 0)
            uri.filename = u;
        else
            uri.filename = u.substring(pos + 1);
    }
    var index = uri.filename.lastIndexOf(".");
    if (index != -1) {
        uri.filetype = uri.filename.substring(index+1).toLowerCase();
    }
    return uri;
};
var isIe = false;
if (!!window.ActiveXObject || "ActiveXObject" in window) {
    isIe = true;
}
var getFileSizeFn = function(fileInputId){
    var file = document.getElementById(fileInputId)
    var fileSize = 0;
    if(file){
        if(file.files){
            if(file.files.length>0){
                fileSize = file.files[0].size;
            }
        } else if(isIe){
            file.select();
            var fileobject = new ActiveXObject ("Scripting.FileSystemObject");
            var file = fileobject.GetFile (file.value);
            fileSize = file.Size;
        }
    }
    return fileSize;
};

var hasFlash = (function() {
    if (/*@cc_on!@*/0) {//ie
        return new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
    } else {
        if (navigator.plugins && navigator.plugins.length > 0) {
            return navigator.plugins["Shockwave Flash"];
        }
    }
    return 0;
}());

var hasFormData = (typeof FormData != 'undefined');
var isCanUploadFileAsync = hasSetRequestHeader && hasFormData;
var isCanUploadFile = isCanUploadFileAsync || hasFlash;
var uploadUrl = '';
var parseUploadResponse = function(response) {
    return response.indexOf('callback') > -1 ? //lte ie9
        response.slice(9, -1) : response;
}
var parseDownloadResponse = function(response) {
    return ((response && response.type && response.type === 'application/json') 
        || 0 > Object.prototype.toString.call(response).indexOf('Blob')) ? 
            this.url+'?token=' : window.URL.createObjectURL(response);
}
var uploadFn = function(options) {
	options = options || {};
    options.onFileUploadProgress = options.onFileUploadProgress || progressFn;
    options.onFileUploadComplete = options.onFileUploadComplete || emptyFn;
    options.onFileUploadError = options.onFileUploadError || emptyFn;
    options.onFileUploadCanceled = options.onFileUploadCanceled || emptyFn;
    var acc = options.accessToken;
    if (!acc) {
        options.onFileUploadError({
            type : EASEMOB_IM_UPLOADFILE_NO_LOGIN,
            msg : '用户未登录到usergrid服务器,无法使用文件上传功能'
        });
        return;
    }

    orgName = options.orgName || '';
    appName = options.appName || '';
    appKey = options.appKey || '';
    if(!orgName && !appName && appKey){
        var devInfos = appKey.split('#');
        if(devInfos.length==2){
            orgName = devInfos[0];
            appName = devInfos[1];
        }
    }
    if(!orgName && !appName){
		options.onFileUploadError({
            type : EASEMOB_IM_UPLOADFILE_ERROR,
            msg : '没有指定开发者信息'
        });
        return;
    }
    var apiUrl = options.apiUrl || 'http://a1.easemob.com';
    var uploadUrl = apiUrl + '/' + orgName + '/' + appName + '/chatfiles';
    if (!isCanUploadFileAsync) {
		if(hasFlash && typeof options.flashUpload === 'function') {
            options.flashUpload && options.flashUpload(uploadUrl, options); 
        } else {
            this.onError({
                type : EASEMOB_IM_UPLOADFILE_BROWSER_ERROR,
                msg : '当前浏览器不支持异步上传！'
            });
        }
        return;
    }

    
    var fileSize = getFileSizeFn(options.fileInputId);
    if(fileSize > EASEMOB_IM_FILESIZE_LIMIT){
        options.onFileUploadError({
            type : EASEMOB_IM_UPLOADFILE_ERROR,
            msg : '上传文件超过服务器大小限制（10M）'
        });
        return ;
    }else if(fileSize <= 0){
        options.onFileUploadError({
            type : EASEMOB_IM_UPLOADFILE_ERROR,
            msg : '上传文件大小为0'
        });
        return ;
    }

    var xhr = xmlrequest();
    var onError = function(e) {
        options.onFileUploadError({
            type : EASEMOB_IM_UPLOADFILE_ERROR,
            msg : '上传文件失败',
            e : e,
            xhr : xhr
        });
    }
    if(xhr.upload){
		
		if("progress" in  xhr.upload){
			delete xhr.upload.progress;
		}	
		xhr.upload.addEventListener("progress",options.onFileUploadProgress, false);
    }
    if(xhr.addEventListener){
        xhr.addEventListener("abort", options.onFileUploadCanceled, false);
        xhr.addEventListener("load", function(e) {
            try{
                var json = $.parseJSON(xhr.responseText);
                options.onFileUploadComplete(json);
            } catch(e){
                options.onFileUploadError({
                    type : EASEMOB_IM_UPLOADFILE_ERROR,
                    msg : '上传文件失败,服务端返回值值不正确',
                    e : e,
                    data : xhr.responseText,
                    xhr : xhr
                });
            }
        }, false);
        xhr.addEventListener("error", onError, false);
    } else if(xhr.onreadystatechange){
        xhr.onreadystatechange = function (){
            if( xhr.readyState === 4){
                if (ajax.status == 200) {
                    try{
                        var json = $.parseJSON(xhr.responseText);
                        options.onFileUploadComplete(json);
                    } catch(e){
                        options.onFileUploadError({
                            type : EASEMOB_IM_UPLOADFILE_ERROR,
                            msg : '上传文件失败,服务端返回值不正确',
                            e : e,
                            data : xhr.responseText,
                            xhr : xhr
                        });
                    }
                } else {
                        options.onFileUploadError({
                            type : EASEMOB_IM_UPLOADFILE_ERROR,
                            msg : '上传文件失败,服务端返回异常',
                            data : xhr.responseText,
                            xhr : xhr
                        });
                }
            } else {
                xhr.abort();
                options.onFileUploadCanceled();
            }
        }
    }

    xhr.open("POST", uploadUrl);
	xhr.withCredentials=true;
    xhr.setRequestHeader('restrict-access', 'true');
    xhr.setRequestHeader('Authorization', 'Bearer ' + acc);

    var localFile = '';
    var fileInput = document.getElementById(options.fileInputId);
    var localFile = null;
    if ("files" in fileInput) {
        localFile = fileInput.files[0];
    } else {
        localFile = fileInput.value;
    }
    var formData = new FormData();
    formData.append("file", localFile);
    //xhr.send(formData);
	
	$.ajax({
       url: uploadUrl,
	   type: "POST",
	   processData: false,
	   contentType: false,
	   data: formData,
	   success: function(data) {
		   
	       options.onFileUploadComplete(data);
	   }
    });
};

var hasBlob = (typeof Blob != 'undefined');
var isCanDownLoadFile = (hasSetRequestHeader && (hasBlob || hasOverrideMimeType));
var downloadFn = function(options){
    options.onFileDownloadComplete = options.onFileDownloadComplete || emptyFn;
    options.onFileDownloadError = options.onFileDownloadError || emptyFn;
    
    var accessToken = options.accessToken || '';
    if (!accessToken) {
        options.onFileDownloadError({
            type : EASEMOB_IM_DOWNLOADFILE_NO_LOGIN,
            msg : '用户未登录到usergrid服务器,无法使用文件下载功能'
        });
        return;
    }

    var onError = function(e) {
        options.onFileDownloadError({
            type : EASEMOB_IM_DOWNLOADFILE_ERROR,
            msg : '下载文件失败',
            xhr : xhr,
            e : e
        });
    }
    if (!isCanDownLoadFile) {
        options.onFileDownloadComplete();
        return;
    }
    var xhr = xmlrequest();
    if("addEventListener" in xhr){
        xhr.addEventListener("load", function(e) {
            options.onFileDownloadComplete(xhr.response,xhr);
        }, false);
        xhr.addEventListener("error", onError, false);
    } else if("onreadystatechange" in xhr){
        xhr.onreadystatechange = function (){
            if( xhr.readyState === 4){
                if (ajax.status == 200) {
                    options.onFileDownloadComplete(xhr.response,xhr);
                } else {
                        options.onFileDownloadError({
                            type : EASEMOB_IM_DOWNLOADFILE_ERROR,
                            msg : '下载文件失败,服务端返回异常',
                            xhr : xhr
                        });
                }
            } else {
                xhr.abort();
                options.onFileDownloadError({
                    type : EASEMOB_IM_DOWNLOADFILE_ERROR,
                    msg : '错误的下载状态,退出下载',
                    xhr : xhr
                });
            }
        }
    }

    var method = options.method || 'GET';
    var resType = options.responseType || 'blob';
    var mimeType = options.mimeType || "text/plain; charset=x-user-defined";
    xhr.open(method, options.url);
    if(typeof Blob != 'undefined'){
        xhr.responseType = resType;
    } else {
        xhr.overrideMimeType(mimeType);
    }

    var innerHeaer = {
        'X-Requested-With' : 'XMLHttpRequest',
        'Accept' : 'application/octet-stream',
        'share-secret' : options.secret,
        'Authorization' : 'Bearer ' + accessToken
    };
    var headers = options.headers || {};
    for(var key in headers){
        innerHeaer[key] = headers[key];
    }
    for(var key in innerHeaer){
        if(innerHeaer[key]){
            xhr.setRequestHeader(key, innerHeaer[key]);
        }
    }
    xhr.send(null);
};

var parseNameFromJidFn = function(jid,domain){
    domain = domain || "";
    var tempstr = jid;
    var findex = tempstr.indexOf("_");
    if(findex!=-1){
        tempstr = tempstr.substring(findex+1);
    }
    var atindex = tempstr.indexOf("@" + domain);
    if(atindex!=-1){
        tempstr = tempstr.substring(0,atindex);
    }
    return tempstr;
};

var parseTextMessageFn = function(message){
    if(typeof(message) != 'string'){
        conn.onError({
            type : EASEMOB_IM_MESSAGE_REC_TEXT_ERROR,
            msg : '不合法的消息内容格式，请检查发送消息内容！'
        });
        return;
    }
    var receiveMsg = message;
    var emessage = [];
    var expr = /\[[^[\]]{2,3}\]/mg;
    var emotions = receiveMsg.match(expr);
    if (!emotions || emotions.length < 1){
        return {"isemotion":false,"body":[{"type" : "txt","data":message}]};
    }
    var isemotion = false;
    for (var i = 0; i < emotions.length; i++) {
        var tmsg = receiveMsg.substring(0,receiveMsg.indexOf(emotions[i]));
        if (tmsg) {
            emessage.push({
                "type" : "txt",
                "data" : tmsg
            });
        }
        var emotion = emotionPicData[emotions[i]];
        if (emotion) {
            isemotion = true;
            emessage.push({
                "type" : "emotion",
                "data" : emotion
            });
        } else {
            emessage.push({
                "type" : "txt",
                "data" : emotions[i]
            });
        }
        var restMsgIndex = receiveMsg.indexOf(emotions[i]) + emotions[i].length;
        receiveMsg = receiveMsg.substring(restMsgIndex);
    }
    if (receiveMsg) {
        emessage.push({
            "type" : "txt",
            "data" : receiveMsg
        });
    }
    if(isemotion){
        return {"isemotion":isemotion,"body":emessage};
    }
    return {"isemotion":false,"body":[{"type" : "txt","data":message}]};
}

var parseResponseMessageFn = function(msginfo){
    var parseMsgData = {errorMsg:true,data:[]};

    var msgBodies = msginfo.getElementsByTagName("body");
    if(msgBodies){
        for (var i=0;i<msgBodies.length;i++){
            var msgBody = msgBodies[i];
            var childNodes = msgBody.childNodes;
            if(childNodes && childNodes.length>0){
                var childNode = msgBody.childNodes[0];
                if(childNode.nodeType==Strophe.ElementType.TEXT){
                    var jsondata = childNode.wholeText ||childNode.nodeValue;
                    jsondata = jsondata.replace('\n','<br>');
                    try{
                        var data = eval("("+jsondata+")");
                        parseMsgData.errorMsg = false;
                        parseMsgData.data = [data];
                    }catch(e){
                    }
                }
            }
        }
        var delayTags = msginfo.getElementsByTagName("delay");
        if(delayTags && delayTags.length>0){
            var delayTag = delayTags[0];
            var delayMsgTime = delayTag.getAttribute("stamp");
            if(delayMsgTime){
                parseMsgData.delayTimeStamp = delayMsgTime;
            }
        }
    } else {
        var childrens = msginfo.childNodes;
        if(childrens&&childrens.length>0){
            var child = msginfo.childNodes[0];
            if(child.nodeType==Strophe.ElementType.TEXT){
                try{
                    var data = eval("("+child.nodeValue+")");
                    parseMsgData.errorMsg = false;
                    parseMsgData.data = [data];
                } catch(e){
                }

            }
        }
    }
    return parseMsgData;
};
var parseFriendFn = function(queryTag){
    var rouster = [];
    var items = queryTag.getElementsByTagName("item");
    if(items){
        for(var i=0;i<items.length;i++){
            var item = items[i];
            var jid = item.getAttribute('jid');
            if(!jid){
                continue;
            }
            var subscription = item.getAttribute('subscription');
            var friend = {
                        subscription : subscription,
                        jid : jid
            };
            var ask = item.getAttribute('ask');
            if(ask){
                friend.ask = ask;
            }
            var name = item.getAttribute('name');
            if(name){
                friend.name = name;
            } else {
                var n = parseNameFromJidFn(jid);
                friend.name = n;
            }
            var groups = [];
            Strophe.forEachChild(item, 'group',function(group){
                groups.push(Strophe.getText(group));
            });
            friend.groups = groups;
            rouster.push(friend);
        }
    }
    return rouster;
};
var parseRoomFn = function(result){
    var rooms = [];
    var items = result.getElementsByTagName("item");
    if(items){
        for(var i=0;i<items.length;i++){
            var item = items[i];
            var roomJid = item.getAttribute('jid');
            var tmp = roomJid.split("@")[0];
            var room = {
                    jid : roomJid,
                    name : item.getAttribute('name'),
                    roomId : tmp.split('_')[1]
                };
            rooms.push(room);
        }
    }
    return rooms;
};
var parseRoomOccupantsFn = function(result){
    var occupants = [];
    var items = result.getElementsByTagName("item");
    if(items){
        for(var i=0;i<items.length;i++){
            var item = items[i];
            var room = {
                    jid : item.getAttribute('jid'),
                    name : item.getAttribute('name')
                };
            occupants.push(room);
        }
    }
    return occupants;
}
var login2UserGrid = function(options){
    options = options || {};

    var appKey = options.appKey || '';
    var devInfos = appKey.split('#');
    if(devInfos.length!=2){
        error({
            type : EASEMOB_IM_CONNCTION_OPEN_USERGRID_ERROR,
            msg : '请指定正确的开发者信息(appKey)'
        });
        return false;
    }
    var orgName = devInfos[0];
    var appName = devInfos[1];
    if(!orgName){
        error({
            type : EASEMOB_IM_CONNCTION_OPEN_USERGRID_ERROR,
            msg : '请指定正确的开发者信息(appKey)'
        });
        return false;
    }
    if(!appName){
        error({
            type : EASEMOB_IM_CONNCTION_OPEN_USERGRID_ERROR,
            msg : '请指定正确的开发者信息(appKey)'
        });
        return false;
    }
    var suc = options.success || emptyFn;
    var error = options.error || emptyFn;
    var user = options.user || '';
    var pwd = options.pwd || '';

    var https = options.https;
    var url = https ? 'https://a1.easemob.com' : 'http://a1.easemob.com';
    var apiUrl = options.apiUrl || url;

    return dologin2UserGrid(apiUrl,user,pwd,orgName,appName,suc,error);
};
var dologin2UserGrid = function(apiUrl,user,pwd,orgName,appName,suc,error) {
    var loginJson = {
        grant_type : 'password',
        username : user,
        password : pwd
    };
    var loginfo = JSON.stringify(loginJson);

    var options = {
        url : apiUrl+"/"+orgName+"/"+appName+"/token",
        dataType : 'json',
        data : loginfo,
        success : suc || emptyFn,
        error : error || emptyFn
    };
    var param = doAjaxRequest(options);
    return param;
};

var innerCheck = function(options,conn){
    if (conn.isOpened() || conn.isOpening()) {
        conn.onError({
            type : EASEMOB_IM_CONNCTION_REOPEN_ERROR,
            msg : '重复打开连接,请先关闭连接再打开'
        });
        return false;
    }
    options = options || {};

    var user = options.user || '';
    if (options.user == '') {
        conn.onError({
            type : EASEMOB_IM_CONNCTION_USER_NOT_ASSIGN_ERROR,
            msg : '未指定用户'
        });
        return false;
    }

    var appKey = options.appKey || "";
    var devInfos = appKey.split('#');
    if(devInfos.length!=2){
        conn.onError({
            type : EASEMOB_IM_CONNCTION_OPEN_ERROR,
            msg : '请指定正确的开发者信息(appKey)'
        });
        return false;
    }
    var orgName = devInfos[0];
    var appName = devInfos[1];
    if(!orgName){
        conn.onError({
            type : EASEMOB_IM_CONNCTION_OPEN_ERROR,
            msg : '请指定正确的开发者信息(appKey)'
        });
        return false;
    }
    if(!appName){
        conn.onError({
            type : EASEMOB_IM_CONNCTION_OPEN_ERROR,
            msg : '请指定正确的开发者信息(appKey)'
        });
        return false;
    }
    
    // jid = {appkey}_{username}@domain/resource
    var jid = appKey + "_" + user + "@" + conn.domain;

    //var resource_value = Math.floor(Math.random()*1000);
    var resource_value = "webim";
    
    var resource = options.resource || resource_value;
    if(resource != ""){
        jid = jid + "/" + resource;
    }
    conn.context.jid = jid;
    conn.context.userId = user;
    conn.context.appKey = appKey;
    conn.context.appName = appName;
    conn.context.orgName = orgName;
    
    return true;
};
var parseMessageType = function(msginfo){
    var msgtype = 'normal';
    var receiveinfo = msginfo.getElementsByTagName("received");
    if(receiveinfo && receiveinfo.length > 0 && receiveinfo[0].namespaceURI == "urn:xmpp:receipts"){
        msgtype = 'received';
    }else{
        var inviteinfo =  msginfo.getElementsByTagName("invite");
        if(inviteinfo && inviteinfo.length > 0){
            msgtype = 'invite';
        }
    }
    return msgtype;
};
var login2ImCallback = function (status,msg,conn){
    if (status == Strophe.Status.CONNFAIL){
      conn.onError({
            type : EASEMOB_IM_CONNCTION_SERVER_CLOSE_ERROR,
            msg : msg
        });
    } else if ((status == Strophe.Status.ATTACHED) || (status == Strophe.Status.CONNECTED)){
        var handleMessage = function(msginfo){
            var type = parseMessageType(msginfo);
            if('received' == type){
                conn.handleReceivedMessage(msginfo);
                return true;
            }else if('invite' == type){
                conn.handleInviteMessage(msginfo);
                return true;
            }else{
                conn.handleMessage(msginfo);
                return true;
            }
        };
        var handlePresence = function(msginfo){
            conn.handlePresence(msginfo);
            return true;
        };
        var handlePing = function(msginfo){
            conn.handlePing(msginfo);
            return true;
        };
        var handleIq = function(msginfo){
            conn.handleIq(msginfo);
            return true;
        };

        conn.addHandler(handleMessage, null, 'message', null, null,  null);
        conn.addHandler(handlePresence, null, 'presence', null, null,  null);
        conn.addHandler(handlePing, "urn:xmpp:ping", 'iq', "get", null,  null);
        conn.addHandler(handleIq, "jabber:iq:roster", 'iq', "set", null,  null);

        conn.context.status = STATUS_OPENED;
        var supportRecMessage = [
           EASEMOB_IM_MESSAGE_REC_TEXT,
           EASEMOB_IM_MESSAGE_REC_EMOTION ];
        if (isCanDownLoadFile) {
            supportRecMessage.push(EASEMOB_IM_MESSAGE_REC_PHOTO);
            supportRecMessage.push(EASEMOB_IM_MESSAGE_REC_AUDIO_FILE);
        }
        var supportSedMessage = [ EASEMOB_IM_MESSAGE_SED_TEXT ];
        if (isCanUploadFile) {
            supportSedMessage.push(EASEMOB_IM_MESSAGE_REC_PHOTO);
            supportSedMessage.push(EASEMOB_IM_MESSAGE_REC_AUDIO_FILE);
        }
        conn.notifyVersion();
        conn.onOpened({
            canReceive : supportRecMessage,
            canSend : supportSedMessage,
            accessToken : conn.context.accessToken
        });
    } else if (status == Strophe.Status.DISCONNECTING) {
        if(conn.isOpened()){// 不是主动关闭
            conn.context.status = STATUS_CLOSING;
            conn.onError({
                type : EASEMOB_IM_CONNCTION_SERVER_CLOSE_ERROR,
                msg : msg
            });
        }
    } else if (status == Strophe.Status.DISCONNECTED) {
        conn.context.status = STATUS_CLOSED;
        conn.clear();
        conn.onClosed();
    } else if (status == Strophe.Status.AUTHFAIL){
        conn.onError({
            type : EASEMOB_IM_CONNCTION_AUTH_ERROR,
            msg : '登录失败,请输入正确的用户名和密码'
        });
        conn.clear();
    } else if(status == Strophe.Status.ERROR){
        conn.onError({
            type : EASEMOB_IM_CONNCTION_SERVER_ERROR,
            msg : msg || '服务器异常'
        });
    }
};
var getJid = function(options,conn){
    var jid = options.toJid || '';
    if(jid==''){
        var appKey = conn.context.appKey || '';
        var toJid = appKey + "_" + options.to + "@"
                + conn.domain;
        if(options.resource){
            toJid = toJid + "/" + options.resource;
        }
        jid = toJid;
    }
    return jid;
};

tempIndex = 0;
var STATUS_INIT = tempIndex++;
var STATUS_DOLOGIN_USERGRID = tempIndex++;
var STATUS_DOLOGIN_IM = tempIndex++;
var STATUS_OPENED = tempIndex++;
var STATUS_CLOSING = tempIndex++;
var STATUS_CLOSED = tempIndex++;

var connection = function() {
}
connection.prototype.init = function(options) {
    if (window.WebSocket) {
        this.url = options.url || (options.https ? 'wss' : 'ws') + '://im-api.easemob.com/ws/';
    } else {
        this.url = ((options.url && options.url.indexOf('ws') > -1) ? '' : options.url) || (options.https ? 'https' : 'http') + '://im-api.easemob.com/http-bind/';
    }

    this.https = options.https || false;
    this.wait = options.wait || 30;
    this.hold = options.hold || 1;
    if(options.route){
        this.route = options.route;
    }

    this.domain = options.domain || "easemob.com";
    this.inactivity = options.inactivity || 30;
    this.maxRetries = options.maxRetries || 5;
    this.pollingTime = options.pollingTime || 800;
    this.stropheConn = false;
    this.onOpened = options.onOpened || emptyFn;
    this.onClosed = options.onClosed || emptyFn;
    this.onTextMessage = options.onTextMessage || emptyFn;
    this.onEmotionMessage = options.onEmotionMessage || emptyFn;
    this.onPictureMessage = options.onPictureMessage || emptyFn;
    this.onAudioMessage = options.onAudioMessage || emptyFn;
    this.onVideoMessage = options.onVideoMessage || emptyFn;
    this.onFileMessage = options.onFileMessage || emptyFn;
    this.onLocationMessage = options.onLocationMessage || emptyFn;
    this.onCmdMessage = options.onCmdMessage || emptyFn;
    this.onPresence = options.onPresence || emptyFn;
    this.onRoster = options.onRoster || emptyFn;
    this.onError = options.onError || emptyFn;
    this.onReceivedMessage = options.onReceivedMessage || emptyFn;
    this.onInviteMessage = options.onInviteMessage || emptyFn;

    this.context = {
        status : STATUS_INIT
    };
}
var dologin2IM = function(options,conn){
    var accessToken = options.access_token || '';
    if(accessToken == ''){
        var loginfo = JSON.stringify(options);
        conn.onError({
            type : EASEMOB_IM_CONNCTION_OPEN_USERGRID_ERROR,
            msg : "登录失败,"+ loginfo,
            data : options,
            xhr : xhr
        });
        return;
    }
    conn.context.accessToken = options.access_token;
    conn.context.accessTokenExpires = options.expires_in;
    var stropheConn = conn.context.stropheConn || new Strophe.Connection(conn.url,{
                        inactivity : conn.inactivity,
                        maxRetries : conn.maxRetries,
                        pollingTime : conn.pollingTime
    });
    var callback = function(status,msg){
        login2ImCallback(status,msg,conn);
    };
    var jid = conn.context.jid;
    conn.context.stropheConn = stropheConn;
    if(conn.route){
        stropheConn.connect(jid,"$t$" + accessToken,callback,conn.wait,conn.hold,conn.route);
    } else {
        stropheConn.connect(jid,"$t$" + accessToken,callback,conn.wait,conn.hold);
    }

};
// user, pwd, appKey, resource
connection.prototype.open = function(options) {
    var pass = innerCheck(options,this);
    if(pass == false){
        return;
    }
    
    var conn = this;
    if(options.accessToken){
        options.access_token = options.accessToken;
        dologin2IM(options,conn);
    }else{
        var loginUrl = this.https ? "https://a1.easemob.com" : "http://a1.easemob.com";
        var apiUrl = options.apiUrl || loginUrl;
        var userId = this.context.userId;
        var pwd = options.pwd || '';
        var appName = this.context.appName;
        var orgName = this.context.orgName;

        var suc = function(data,xhr){
            conn.context.status = STATUS_DOLOGIN_IM;
            dologin2IM(data,conn);
        };
        var error = function(res,xhr,msg){
            if(res.error && res.error_description){
                conn.onError({
                    type : EASEMOB_IM_CONNCTION_OPEN_USERGRID_ERROR,
                    msg : "登录失败,"+res.error_description,
                    data : res,
                    xhr : xhr
                });
            } else {
                conn.onError({
                    type : EASEMOB_IM_CONNCTION_OPEN_USERGRID_ERROR,
                    msg : "登录失败",
                    data : res,
                    xhr : xhr
                });
            }
            conn.clear();
        };
        this.context.status = STATUS_DOLOGIN_USERGRID;
        dologin2UserGrid(apiUrl,userId,pwd,orgName,appName,suc,error);
    }

};
connection.prototype.attach = function(options) {
    var pass = innerCheck(options,this);
    if(pass == false)
        return;{
    }
    options = options || {};

    var accessToken = options.accessToken || '';
    if(accessToken == ''){
        this.onError({
            type : EASEMOB_IM_CONNCTION_ATTACH_USERGRID_ERROR,
            msg : '未指定用户的accessToken'
        });
        return;
    }

    var sid = options.sid || '';
    if(sid == ''){
        this.onError({
            type : EASEMOB_IM_CONNCTION_ATTACH_ERROR,
            msg : '未指定用户的会话信息'
        });
        return;
    }

    var rid = options.rid || '';
    if(rid == ''){
        this.onError({
            type : EASEMOB_IM_CONNCTION_ATTACH_ERROR,
            msg : '未指定用户的消息id'
        });
        return;
    }

    var stropheConn = new Strophe.Connection(this.url,{
                        inactivity : this.inactivity,
                        maxRetries : this.maxRetries,
                        pollingTime : this.pollingTime
    });

    this.context.accessToken = accessToken;
    this.context.stropheConn = stropheConn;
    this.context.status = STATUS_DOLOGIN_IM;

    var conn = this;
    var callback = function(status,msg){
        login2ImCallback(status,msg,conn);
    };
    var jid = this.context.jid;
    var wait = this.wait;
    var hold = this.hold;
    var wind = this.wind || 5;
    stropheConn.attach(jid, sid, rid, callback, wait, hold, wind);
};
connection.prototype.close = function() {
    var status = this.context.status;
    if (status==STATUS_INIT) {
        return;
    }
    if(this.isClosed() || this.isClosing()){
        return;
    }
    this.context.status = STATUS_CLOSING;
    this.context.stropheConn.disconnect();
};
// see stropheConn.addHandler
connection.prototype.addHandler = function (handler, ns, name, type, id, from, options){
    this.context.stropheConn.addHandler(handler, ns, name, type, id, from, options);
};
connection.prototype.notifyVersion = function (suc,fail){
    var jid = getJid({},this);
    var dom = $iq({
            from : this.context.jid || '',
            to: this.domain,
            type: "result"
    }).c("query", {xmlns: "jabber:iq:version"}).c("name").t("easemob").up().c("version").t(Easemob.im.version).up().c("os").t("webim");
    suc = suc || emptyFn;
    error = fail || this.onError;
    var failFn = function(ele){
        error({
            type : EASEMOB_IM_CONNCTION_NOTIFYVERSION_ERROR,
            msg : '发送版本信息给服务器时失败',
            data : ele
        });
    };
    this.context.stropheConn.sendIQ(dom.tree(),suc,failFn);
    return;
};
connection.prototype.handlePresence = function(msginfo){
    if(this.isClosed()){
        return;
    }
    var from = msginfo.getAttribute('from') || '';
    var to = msginfo.getAttribute('to') || '';
    var type = msginfo.getAttribute('type') || '';
    var fromUser = parseNameFromJidFn(from);
    var toUser = parseNameFromJidFn(to);
    var info = {
        from: fromUser,
        to : toUser,
        fromJid : from,
        toJid : to,
        type : type
    };

    var showTags = msginfo.getElementsByTagName("show");
    if(showTags && showTags.length>0){
        var showTag = showTags[0];
        info.show = Strophe.getText(showTag);
    }
    var statusTags = msginfo.getElementsByTagName("status");
    if(statusTags && statusTags.length>0){
        var statusTag = statusTags[0];
        info.status = Strophe.getText(statusTag);
    }

    var priorityTags = msginfo.getElementsByTagName("priority");
    if(priorityTags && priorityTags.length>0){
        var priorityTag = priorityTags[0];
        info.priority  = Strophe.getText(priorityTag);
    }
    this.onPresence(info,msginfo);
};
connection.prototype.handlePing = function(e) {
    if(this.isClosed()){
        return;
    }
    var id = e.getAttribute('id');
    var from = e.getAttribute('from');
    var to = e.getAttribute('to');
    var dom = $iq({
                from : to,
                to : from,
                id : id,
                type : 'result'
    });
    this.sendCommand(dom.tree());
};
connection.prototype.handleIq = function(e) {
    var id = e.getAttribute('id');
    var from = e.getAttribute('from') || '';
    var name = parseNameFromJidFn(from);
    var curJid = this.context.jid;
    var curUser = this.context.userId;
    if (from !== "" && from != curJid && curUser != name)
        return true;

    var iqresult = $iq({type: 'result', id: id, from: curJid});
    this.sendCommand(iqresult.tree());

    var msgBodies = e.getElementsByTagName("query");
    if(msgBodies&&msgBodies.length>0){
        var queryTag = msgBodies[0];
        var rouster = parseFriendFn(queryTag);
        this.onRoster(rouster);
    }
    return true;
};
connection.prototype.handleMessage = function(msginfo){
    if(this.isClosed()){
        return;
    }
    var id = msginfo.getAttribute('id') || '';
    this.sendReceiptsMessage({
        id : id
    });
    var parseMsgData = parseResponseMessageFn(msginfo);
    if(parseMsgData.errorMsg){
        return;
    }
    var msgDatas = parseMsgData.data;
    for(var i in msgDatas){
        var msg = msgDatas[i];
        var from = msg.from;
        var too = msg.to;
        var extmsg = msg.ext || {};
        var chattype = msginfo.getAttribute('type') || 'chat';
        var msgBodies = msg.bodies;
        if(!msgBodies || msgBodies.length==0){
            continue;
        }
        var msgBody = msg.bodies[0];
        var type = msgBody.type;
        if ("txt" == type) {
            var receiveMsg = msgBody.msg;
            var emotionsbody = parseTextMessageFn(receiveMsg);
            if(emotionsbody.isemotion){
                this.onEmotionMessage({
                    id: id,
                    type : chattype,
                    from : from,
                    to : too,
                    data : emotionsbody.body,
                    ext : extmsg
                });
            } else {
                this.onTextMessage({
                    id: id,
                    type : chattype,
                    from : from,
                    to : too,
                    data : receiveMsg,
                    ext : extmsg
                });
            }
        } else if ("img" == type) {
            var rwidth = 0;
            var rheight = 0;
            if(msgBody.size){
                rwidth = msgBody.size.width;
                rheight = msgBody.size.height;
            }
            var msg = {
                id: id,
                type : chattype,
                from : from,
                to : too,
                url : msgBody.url,
                secret : msgBody.secret,
                filename : msgBody.filename,
                thumb : msgBody.thumb,
                thumb_secret : msgBody.thumb_secret,
                file_length : msgBody.file_length||'',
                width : rwidth,
                height : rheight,
                filetype : msgBody.filetype||'',
                accessToken : this.context.accessToken || '',
                ext : extmsg
            };
            this.onPictureMessage(msg);
        } else if ("audio" == type) {
            this.onAudioMessage({
                id: id,
                type : chattype,
                from : from,
                to : too,
                url : msgBody.url,
                secret : msgBody.secret,
                filename : msgBody.filename,
                length : msgBody.length||'',
                file_length : msgBody.file_length||'',
                filetype : msgBody.filetype||'',
                accessToken : this.context.accessToken || '',
                ext : extmsg
            });
        } else if ("file" == type) {
            this.onFileMessage({
                id: id,
                type : chattype,
                from : from,
                to : too,
                url : msgBody.url,
                secret : msgBody.secret,
                filename : msgBody.filename,
                file_length : msgBody.file_length,
                accessToken : this.context.accessToken || '',
                ext : extmsg
            });
        } else if ("loc" == type) {
            this.onLocationMessage({
                id: id,
                type : chattype,
                from : from,
                to : too,
                addr : msgBody.addr,
                lat : msgBody.lat,
                lng : msgBody.lng,
                ext : extmsg
            });
        }else if("video" == type){
            this.onVideoMessage({
                id: id,
                type : chattype,
                from : from,
                to : too,
                url : msgBody.url,
                secret : msgBody.secret,
                filename : msgBody.filename,
                file_length : msgBody.file_length,
                accessToken : this.context.accessToken || '',
                ext : extmsg
            });
        }else if("cmd" == type){
            this.onCmdMessage({
                id: id,
                from : from,
                to : too,
                action : msgBody.action,
                ext : extmsg
            });
        }
    }
};
connection.prototype.handleReceivedMessage = function(message){
    this.onReceivedMessage(message);
};
connection.prototype.handleInviteMessage = function(message){
    var form = null;
    var invitemsg = message.getElementsByTagName('invite');
    if(invitemsg && invitemsg.length>0){
        var fromJid = invitemsg[0].getAttribute('from');
        form = parseNameFromJidFn(fromJid);
    }
    var xmsg = message.getElementsByTagName('x');
    var roomid = null;
    if(xmsg && xmsg.length > 0){
        for(var i = 0; i < xmsg.length; i++){
            if('jabber:x:conference' == xmsg[i].namespaceURI){
                var roomjid = xmsg[i].getAttribute('jid');
                roomid = parseNameFromJidFn(roomjid);
            }
        }
    }
    this.onInviteMessage({
        type : 'invite',
        from : form,
        roomid : roomid
    });
};
connection.prototype.sendCommand = function(dom) {
    if(this.isOpened()){
        this.context.stropheConn.send(dom);
    } else {
        this.onError({
            type : EASEMOB_IM_CONNCTION_OPEN_ERROR,
            msg : '连接还未建立,请先登录或等待登录处理完毕'
        });
    }
};
connection.prototype.getUniqueId = function (prefix)
{
    var cdate = new Date();
    var offdate = new Date(2010,1,1);
        var offset = cdate.getTime()-offdate.getTime();
        var hexd = parseInt(offset).toString(16);
    if (typeof(prefix) == "string" || typeof(prefix) == "number") {
        return prefix+"_"+hexd;
    } else {
        return 'WEBIM_'+hexd;
    }
};
connection.prototype.sendTextMessage = function(options) {
    var appKey = this.context.appKey || '';
    var toJid = appKey + "_" + options.to + "@" + this.domain;
    if(options.type && options.type == 'groupchat'){
        toJid = appKey + "_"+options.to+'@conference.' + this.domain;
    }
    if(options.resource){
        toJid = toJid + "/" + options.resource;
    }
    var msgTxt = options.msg;
    var json = {
        from : this.context.userId || '',
        to :    options.to,
        bodies : [{
            type : "txt",
            msg : msgTxt
        }],
        ext : options.ext || {}
    };
    var jsonstr = JSON.stringify(json);
    var dom = $msg({
        to : toJid,
        type : options.type || 'chat',
        id : this.getUniqueId(),
        xmlns : "jabber:client"
    }).c("body").t(jsonstr);
    this.sendCommand(dom.tree());
};
connection.prototype.heartBeat = function(conn) {
    var options = {
        to : conn.domain,
        type : "normal"
    };
    conn.heartBeatID = setInterval(function() {
        conn.sendHeartBeatMessage(options);
    }, 60000);
}
connection.prototype.sendHeartBeatMessage = function(options) {
    var json = {};
    var jsonstr = JSON.stringify(json);
    var dom = $msg({
        to : options.to,
        type : options.type,
        id : this.getUniqueId(),
        xmlns : "jabber:client"
    }).c("body").t(jsonstr);
    this.sendCommand(dom.tree());
};
connection.prototype.stopHeartBeat = function(conn) {
    clearInterval(conn.heartBeatID);
};
connection.prototype.sendPicture = function(options) {
    var onerror =  options.onFileUploadError || this.onError || emptyFn;
    var onFileUploadComplete = options.onFileUploadComplete || emptyFn;
    var conn = this;
    var myUploadComplete = function(data) {
		options["url"] = data.uri;
        options["secret"] = data.entities[0]["share-secret"];
        if(data.entities[0]["file-metadata"]){
            var file_len = data.entities[0]["file-metadata"]["content-length"];
            options["file_length"] = file_len;
            options["filetype"] = data.entities[0]["file-metadata"]["content-type"]
            if (file_len > 204800) {
                options["thumbnail"] = true;
            }
        }
        options["uuid"] = data.entities[0].uuid;
        onFileUploadComplete(data);
        conn.sendPictureMessage(options);   
   	};
    options.onFileUploadComplete = myUploadComplete;
    options.onFileUploadError = options.onFileUploadError|| this.onError || emptyFn;
	
    /*if(!isCanUploadFileAsync){
        options.appName = conn.context.appName || '';
        options.orgName = conn.context.orgName || '';
        options.accessToken = conn.context.accessToken || '';
		uploadFn.call(this, options);
		return;
    }*/
    
    var image = new Image();
    var imageLoadFn = function() {
        image.onload = null;
        if (!this.readyState || this.readyState == 'loaded'
                || this.readyState == 'complete') {
    
			var heigth = image.height;
            var width = image.width;
            options.height = heigth;
            options.width = width;
            options.appName = conn.context.appName || '';
            options.orgName = conn.context.orgName || '';
            options.accessToken = conn.context.accessToken || '';
            uploadFn(options);
        };
    };
    if("onload" in image){
        image.onload = imageLoadFn;
    } else {
        image.onreadystatechange = imageLoadFn;
    }
    image.onerror = function() {
        image.onerror = function(){
            image.onerror = null;
            options.onFileUploadError({
                type : EASEMOB_IM_UPLOADFILE_ERROR,
                msg : '指定的图片不存在或者不是一个图片格式文件'
            });
        };
        image.src = document.getElementById(options.fileInputId).value;
    };
    var picId = options.fileInputId;
    file = getFileUrlFn(picId);
    options.fileInfo = file;
    options.filename = file.filename;

    if (!file.url) {
        options.onFileUploadError({
            type : EASEMOB_IM_UPLOADFILE_NO_FILE,
            msg : '未选择上传文件'
        });
    } else {
        image.src = file.url;
    }
};
connection.prototype.sendPictureMessage = function(options) {
    var appKey = this.context.appKey || '';
    var toJid = appKey + "_" + options.to + "@" + this.domain;
    if(options.type && options.type == 'groupchat'){
        toJid = appKey + "_"+options.to+'@conference.' + this.domain;
    }
    if(options.resource){
        toJid = toJid + "/" + options.resource;
    }

    var json = {
                from : this.context.userId || '',
                to :    options.to,
                bodies :[{
                        type : "img",
                        url : options.url + '/' + options.uuid,
                        secret : options.secret,
                        filename : options.filename,
                        thumb : options.url + '/' + options.uuid,
                        thumb_secret : '',
                        size : {
                            width : options.width,
                            height : options.height
                        },
                        "file_length" : options.file_length,
                        filetype : options.filetype
                }],
                ext : options.ext || {}
    };
    var jsonstr = JSON.stringify(json);
    var date = new Date();
    var dom = $msg({
                type : options.type || 'chat',
                to : toJid,
                id : this.getUniqueId(),
                xmlns : "jabber:client"
    }).c("body").t(jsonstr);
    this.sendCommand(dom.tree());
};
connection.prototype.sendAudio = function(options) {
    var onerror =  options.onFileUploadError || this.onError || emptyFn;
	var onFileUploadComplete = options.onFileUploadComplete || emptyFn;
    var conn = this;
	var myonComplete = function(data) {
		options["url"] = data.uri;
		options["secret"] = data.entities[0]["share-secret"];
		if(data.entities[0]["file-metadata"]){
			options["file_length"] = data.entities[0]["file-metadata"]["content-length"];
			options["filetype"] = data.entities[0]["file-metadata"]["content-type"];
		}
		options["uuid"] = data.entities[0].uuid;
		options["length"] = data.duration;
		onFileUploadComplete(data);
		conn.sendAudioMessage(options);
	};
	options.appName = this.context.appName || '';
	options.orgName = this.context.orgName || '';
	options.accessToken = this.context.accessToken || '';
	options.onFileUploadComplete = myonComplete;

	if(!isCanUploadFileAsync){
        uploadFn.call(this, options);
        return;
	}

    var file = getFileUrlFn(options.fileInputId);
    options.fileInfo = file;
    options.filename = file.filename;

    uploadFn(options, this);
};
connection.prototype.sendAudioMessage = function(options) {
    var appKey = this.context.appKey || '';
    var toJid = appKey + "_" + options.to + "@" + this.domain;
    if(options.type && options.type == 'groupchat'){
        toJid =appKey + "_"+options.to+'@conference.' + this.domain;
    }
    if(options.resource){
        toJid = toJid + "/" + options.resource;
    }

    var json = {
                from : this.context.userId || '',
                to :    options.to,
                bodies :[{
                        type : "audio",
                        url : options.url + '/' + options.uuid,
                        secret : options.secret,
                        filename : options.filename,
                        "file_length" : options.file_length,
                        length : options.length
                }],
                ext : options.ext || {}
    };
    var jsonstr = JSON.stringify(json);
    var dom = $msg({
                type : options.type || 'chat',
                to : toJid,
                id : this.getUniqueId(),
                xmlns : "jabber:client"
    }).c("body").t(jsonstr);
    this.sendCommand(dom.tree());
};
connection.prototype.sendFileMessage = function(options) {
    var appKey = this.context.appKey || '';
    var toJid = appKey + "_" + options.to + "@" + this.domain;
    if(options.type && options.type == 'groupchat'){
        toJid =appKey + "_"+options.to+'@conference.' + this.domain;
    }

    if(options.resource){
        toJid = toJid + "/" + options.resource;
    }
    var json = {
                from : this.context.userId || '',
                to :    options.to,
                bodies :[{
                        type : "file",
                        url : options.url,
                        secret : options.secret,
                        filename : options.filename,
                        "file_length" : options.file_length
                }],
                ext : options.ext || {}
    };
    var jsonstr = JSON.stringify(json);
    var dom = $msg({
                type : 'chat',
                to : toJid,
                id : this.getUniqueId(),
                xmlns : "jabber:client"
    }).c("body").t(jsonstr);
    this.sendCommand(dom.tree());
};
connection.prototype.sendLocationMessage = function(options) {
    var appKey = this.context.appKey || '';
    var toJid = appKey + "_" + options.to + "@" + this.domain;
    if(options.type && options.type == 'groupchat'){
        toJid =appKey + "_"+options.to+'@conference.' + this.domain;
    }

    if(options.resource){
        toJid = toJid + "/" + options.resource;
    }
    var json = {
                from : this.context.userId || '',
                to :    options.to,
                bodies :[{
                        type : "loc",
                        addr : options.addr,
                        lat : options.lat,
                        lng : options.lng
                }],
                ext : options.ext || {}
    };
    var jsonstr = JSON.stringify(json);
    var dom = $msg({
                type : 'chat',
                to : toJid,
                id : this.getUniqueId(),
                xmlns : "jabber:client"
    }).c("body").t(jsonstr);
    this.sendCommand(dom.tree());
};
connection.prototype.sendReceiptsMessage = function(options){
    var dom = $msg({
                from : this.context.jid || '',
                to : "easemob.com",
                id : options.id || ''
    }).c("received",{
                xmlns : "urn:xmpp:receipts",
                id : options.id || ''
            });
    this.sendCommand(dom.tree());
};
connection.prototype.addRoster = function(options){
    var jid = getJid(options,this);
    var name = options.name || '';
    var groups = options.groups || '';

    var iq = $iq({type : 'set'});
    iq.c("query",{xmlns:'jabber:iq:roster'});
    iq.c("item",{jid: jid ,name : name});

    if(groups){
        for (var i = 0; i < groups.length; i++){
            iq.c('group').t(groups[i]).up();
        }
    }
    var suc = options.success || emptyFn;
    var error = options.error || emptyFn;
    this.context.stropheConn.sendIQ(iq.tree(),suc,error);
};
connection.prototype.removeRoster = function(options){
    var jid = getJid(options,this);
    var iq = $iq({type: 'set'}).c('query', {xmlns : "jabber:iq:roster"}).c('item', {jid: jid,subscription: "remove"});

    var suc = options.success || emptyFn;
    var error = options.error || emptyFn;
    this.context.stropheConn.sendIQ(iq,suc,error);
};
connection.prototype.getRoster = function(options) {
    var conn = this;
    var dom  = $iq({
        type: 'get'
  }).c('query', {xmlns: 'jabber:iq:roster'});

    options = options || {};
    suc = options.success || this.onRoster;
  var completeFn = function(ele){
    var rouster = [];
        var msgBodies = ele.getElementsByTagName("query");
        if(msgBodies&&msgBodies.length>0){
            var queryTag = msgBodies[0];
            rouster = parseFriendFn(queryTag);
        }
    suc(rouster,ele);
  };
  error = options.error || this.onError;
  var failFn = function(ele){
        error({
            type : EASEMOB_IM_CONNCTION_GETROSTER_ERROR,
            msg : '获取联系人信息失败',
            data : ele
        });
  };
    if(this.isOpened()){
        this.context.stropheConn.sendIQ(dom.tree(),completeFn,failFn);
    } else {
        error({
            type : EASEMOB_IM_CONNCTION_OPEN_ERROR,
            msg : '连接还未建立,请先登录或等待登录处理完毕'
        });
    }
};
connection.prototype.subscribe = function(options) {
    var jid = getJid(options,this);
    var pres = $pres({to: jid, type: "subscribe"});
    if (options.message) {
        pres.c("status").t(options.message).up();
    }
    if (options.nick) {
        pres.c('nick', {'xmlns': "http://jabber.org/protocol/nick"}).t(options.nick);
    }
    this.sendCommand(pres.tree());
};
connection.prototype.subscribed = function(options) {
    var jid = getJid(options,this);
    var pres = $pres({to : jid, type : "subscribed"});
    if (options.message) {
        pres.c("status").t(options.message).up();
    }
    this.sendCommand(pres.tree());
};
connection.prototype.unsubscribe = function(options) {
    var jid = getJid(options,this);
    var pres = $pres({to : jid, type : "unsubscribe"});
    if (options.message) {
        pres.c("status").t(options.message);
    }
    this.sendCommand(pres.tree());
};
connection.prototype.unsubscribed = function(options) {
    var jid = getJid(options,this);
    var pres = $pres({to : jid, type : "unsubscribed"});
    if (options.message) {
        pres.c("status").t(options.message).up();
    }
    this.sendCommand(pres.tree());
 };

connection.prototype.createRoom = function(options) {
    var suc =options.success || emptyFn;
    var err =  options.error || emptyFn;
    var roomiq;
    roomiq = $iq({
        to: options.rooomName,
        type: "set"
    }).c("query", {
        xmlns: Strophe.NS.MUC_OWNER
    }).c("x", {
         xmlns: "jabber:x:data",
         type: "submit"
    });
    return this.context.stropheConn.sendIQ(roomiq.tree(), suc, err);
};

connection.prototype.join = function(options){
    var roomJid = this.context.appKey+"_"+options.roomId+'@conference.' + this.domain;
    var room_nick = roomJid+"/"+this.context.userId;
    var suc =options.success || emptyFn;
    var err =  options.error || emptyFn;
    var errorFn = function (ele){
        err({
            type : EASEMOB_IM_CONNCTION_JOINROOM_ERROR,
            msg : '加入房间失败',
            data : ele
        });
    }
    var iq = $pres({
        from: this.context.jid,
        to: room_nick
    }).c("x", {
        xmlns: Strophe.NS.MUC
    });
    this.context.stropheConn.sendIQ(iq.tree(), suc, errorFn);
};
connection.prototype.listRooms = function(options) {
    var iq;
    iq = $iq({
      to: options.server||'conference.' + this.domain,
      from: this.context.jid,
      type: "get"
    }).c("query", {
      xmlns: Strophe.NS.DISCO_ITEMS
    });
    var suc =options.success || emptyFn;
    var completeFn = function(result){
        var rooms = [];
        rooms = parseRoomFn(result);
        suc(rooms);
    }
    var err =  options.error || emptyFn;
    var errorFn = function (ele){
        err({
            type : EASEMOB_IM_CONNCTION_GETROOM_ERROR,
            msg : '获取群组列表失败',
            data : ele
        });
    }
    this.context.stropheConn.sendIQ(iq.tree(), completeFn, errorFn);
};

connection.prototype.queryRoomMember = function(options){
    var domain = this.domain;
    var members = [];
     var iq= $iq({
          to : this.context.appKey+"_"+options.roomId+'@conference.' + this.domain,
          type : 'get'
        }).c('query', {
            xmlns: Strophe.NS.MUC+'#admin'
        }).c('item',{
            affiliation:'member'
        });
    var suc =options.success || emptyFn;
    var completeFn = function(result){
        var items = result.getElementsByTagName('item');
        if(items){
            for(var i=0;i<items.length;i++){
                var item = items[i];
                var mem = {
                        jid : item.getAttribute('jid'),
                        affiliation : 'member'
                    };
                members.push(mem);
            }
        }
        suc(members);
    }
    var err =  options.error || emptyFn;
    var errorFn = function (ele){
        err({
            type : EASEMOB_IM_CONNCTION_GETROOMMEMBER_ERROR,
            msg : '获取群组成员列表失败',
            data : ele
        });
    }
    this.context.stropheConn.sendIQ(iq.tree(), completeFn, errorFn);
};

connection.prototype.queryRoomInfo = function(options){
    var domain = this.domain;
    var iq= $iq({
          to:  this.context.appKey+"_"+options.roomId+'@conference.' + domain,
          type: "get"
        }).c("query", {
          xmlns: Strophe.NS.DISCO_INFO
        });
    var suc =options.success || emptyFn;
    var members = [];
    var completeFn = function(result){
        var fields = result.getElementsByTagName('field');
        if(fields){
            for(var i=0;i<fields.length;i++){
                var field = fields[i];
                if(field.getAttribute('label') == 'owner'){
                    var mem = {
                            jid : (field.textContent||field.text) + "@" + domain,
                            affiliation : 'owner'
                        };
                    members.push(mem);
                }
            }
        }
        suc(members);
    }
    var err =  options.error || emptyFn;
    var errorFn = function (ele){
        err({
            type : EASEMOB_IM_CONNCTION_GETROOMINFO_ERROR,
            msg : '获取群组信息失败',
            data : ele
        });
    }
    this.context.stropheConn.sendIQ(iq.tree(), completeFn, errorFn);
};

connection.prototype.queryRoomOccupants = function(options) {
    var suc =options.success || emptyFn;
    var completeFn = function(result){
        var occupants = [];
        occupants = parseRoomOccupantsFn(result);
        suc(occupants);
    }
    var err =  options.error || emptyFn;
    var errorFn = function (ele){
        err({
            type : EASEMOB_IM_CONNCTION_GETROOMOCCUPANTS_ERROR,
            msg : '获取群组出席者列表失败',
            data : ele
        });
    }
    var attrs = {
      xmlns: Strophe.NS.DISCO_ITEMS
    };
    var info = $iq({
      from : this.context.jid,
      to : this.context.appKey+"_"+options.roomId+'@conference.' + this.domain,
      type : 'get'
    }).c('query', attrs);
    this.context.stropheConn.sendIQ(info.tree(), completeFn, errorFn);
  };

connection.prototype.setUserSig = function(desc) {
    var dom = $pres({xmlns : 'jabber:client'});
    desc = desc || "";
    dom.c("status").t(desc);
    this.sendCommand(dom.tree());
};
connection.prototype.setPresence = function(type,status) {
    var dom = $pres({xmlns : 'jabber:client'});
    if (type){
        if(status){
            dom.c("show").t(type);
            dom.up().c("status").t(status);
        } else {
            dom.c("show").t(type);
        }
    }
    this.sendCommand(dom.tree());
};
connection.prototype.getPresence = function() {
    var dom = $pres({xmlns : 'jabber:client'});
    var conn = this;
    this.sendCommand(dom.tree());
};
connection.prototype.ping = function(options) {
    options = options || {};
    var jid = getJid(options,this);

    var dom = $iq({
        from : this.context.jid || '',
        to: jid,
        type: "get"
    }).c("ping", {xmlns: "urn:xmpp:ping"});

    suc = options.success || emptyFn;
    error = options.error || this.onError;
    var failFn = function(ele){
        error({
            type : EASEMOB_IM_CONNCTION_PING_ERROR,
            msg : 'ping失败',
            data : ele
        });
    };
    if(this.isOpened()){
        this.context.stropheConn.sendIQ(dom.tree(),suc,failFn);
    } else {
        error({
            type : EASEMOB_IM_CONNCTION_OPEN_ERROR,
            msg : '连接还未建立,请先登录或等待登录处理完毕'
        });
    }
    return;
};
connection.prototype.isOpened = function() {
    var status = this.context.status;
    return status==STATUS_OPENED;
};
connection.prototype.isOpening = function() {
    var status = this.context.status;
    return (status==STATUS_DOLOGIN_USERGRID) || (status==STATUS_DOLOGIN_IM);
};
connection.prototype.isClosing = function() {
    var status = this.context.status;
    return (status==STATUS_CLOSING);
};
connection.prototype.isClosed = function() {
    var status = this.context.status;
    return status == STATUS_CLOSED;
};
connection.prototype.clear = function() {
    this.context = {
        status : STATUS_INIT
    };
};


Easemob.im.Connection = connection;

Easemob.im.Helper = Easemob.im.Helper || {};

// method
Easemob.im.Helper.getFileUrl = getFileUrlFn;
Easemob.im.Helper.upload = uploadFn;
Easemob.im.Helper.download = downloadFn;
Easemob.im.Helper.getFileSize = getFileSizeFn;
Easemob.im.Helper.xhr = doAjaxRequest;
Easemob.im.Helper.parseTextMessage = parseTextMessageFn;
Easemob.im.Helper.login2UserGrid = login2UserGrid;
Easemob.im.Helper.parseUploadResponse = parseUploadResponse;
Easemob.im.Helper.parseDownloadResponse = parseDownloadResponse;
Easemob.im.Helper.getIEVersion = getIEVersion;

// attritue
Easemob.im.Helper.isCanUploadFile = isCanUploadFile;
Easemob.im.Helper.isCanUploadFileAsync = isCanUploadFileAsync;
Easemob.im.Helper.isCanDownLoadFile = isCanDownLoadFile;
Easemob.im.Helper.hasSetRequestHeader = hasSetRequestHeader;
Easemob.im.Helper.hasOverrideMimeType = hasOverrideMimeType;
Easemob.im.Helper.uploadUrl = '';

// object
Easemob.im.Helper.Base64 = innerBase64;
Easemob.im.Helper.EmotionPicData = emotionPicData;

//user
Easemob.im.Helper.registerUser = registerUserFn;

}})(jQuery)


