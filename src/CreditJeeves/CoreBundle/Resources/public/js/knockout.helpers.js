function associativeArrayToOptions(obj) {
    var result = [];
    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            result.push({text: obj[prop], value: prop});
        }
    }

    return result;
}
