function associativeArrayToOptions(obj, str) {
    var result = [];
    if (str !== null) {
        result.push({text: str, value: ""});
    }
    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            result.push({text: obj[prop], value: prop});
        }
    }

    return result;
}
