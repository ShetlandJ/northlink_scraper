export const blobToBase64 = (blob) => {
    const reader = new FileReader();
    reader.readAsDataURL(blob);
    return new Promise(resolve => {
        reader.onloadend = () => {
            resolve(reader.result);
        };
    });
};

export const convertDataURIToBinary = (dataURI) => {
    const BASE64_MARKER = ';base64,';
    const base64Index = dataURI.indexOf(BASE64_MARKER) + BASE64_MARKER.length;
    const base64 = dataURI.substring(base64Index);
    const raw = window.atob(base64);
    const rawLength = raw.length;
    const array = new Uint8Array(new ArrayBuffer(rawLength));

    for (let i = 0; i < rawLength; i++) {
        array[i] = raw.charCodeAt(i);
    }
    return array;
}

export const convertToBlogUri = async (blobject) => {
    const dataURI = await blobToBase64(blobject);
    const binary = convertDataURIToBinary(dataURI);
    const blob = new Blob([binary], { type: 'audio/mp3' });
    return URL.createObjectURL(blob);
}

export const indexesOf = (arr, item) =>
  arr.reduce(
    (acc, v, i) => (v === item && acc.push(i), acc),
  []);

export const stringSimilarity = (str1, str2, gramSize = 2) => {
    function getNGrams(s, len) {
        s = ' '.repeat(len - 1) + s.toLowerCase() + ' '.repeat(len - 1);
        let v = new Array(s.length - len + 1);
        for (let i = 0; i < v.length; i++) {
            v[i] = s.slice(i, i + len);
        }
        return v;
    }

    if (!str1?.length || !str2?.length) { return 0.0; }

    //Order the strings by length so the order they're passed in doesn't matter
    //and so the smaller string's ngrams are always the ones in the set
    let s1 = str1.length < str2.length ? str1 : str2;
    let s2 = str1.length < str2.length ? str2 : str1;

    let pairs1 = getNGrams(s1, gramSize);
    let pairs2 = getNGrams(s2, gramSize);
    let set = new Set(pairs1);

    let total = pairs2.length;
    let hits = 0;
    for (let item of pairs2) {
        if (set.delete(item)) {
            hits++;
        }
    }
    return hits / total;
}
