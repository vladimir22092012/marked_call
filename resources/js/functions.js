export function responseToErrors(errors) {
    let errorsList = {}
    const keys = Object.keys(errors)

    keys.forEach(key => {
        errorsList[key] = errors[key][0]
    })

    return errorsList
}
