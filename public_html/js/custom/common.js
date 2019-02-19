/*
 * Include common js for application 
 */
if (!appcore) {
	var appcore = {};
}
appcore.url = function(uri) {
    return appcore.rtrim(appcore.baseUrl, '/') + "/" + appcore.ltrim(uri, '/');
}; 

/**
 * ltrim — Strip whitespace (or other characters) from the beginning of a string
 * @param {string} str
 * @param {string} charlist
 * @returns {String}
 * @see http://phpjs.org/functions/ltrim/
 */
appcore.ltrim = function (str, charlist) {
    charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
    .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
  var re = new RegExp('^[' + charlist + ']+', 'g');
  return (str + '')
    .replace(re, '');
};

/**
 * rtrim — Strip whitespace (or other characters) from the end of a string
 * @param {type} str
 * @param {type} charlist
 * @returns {String}
 * @see http://phpjs.org/functions/rtrim/
 */
appcore.rtrim = function rtrim(str, charlist) {
  charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
    .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
  var re = new RegExp('[' + charlist + ']+$', 'g');
  return (str + '')
    .replace(re, '');
};
