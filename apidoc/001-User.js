/**
 * @api {post} /login 01-Login
 * @apiVersion 1.0.0
 * @apiName User Login
 * @apiGroup 01-Users
 *
 * 
 * 
 * @apiParamExample {json} PARAMETER
 * {
 *      "username": "aeeeeeng",
 *      "password": "sembarang"
 * }
 * 
 * @apiSuccessExample {json} SUCCESS
 * HTTP/1.1 200 OK
 * {
 *      "success": true,
 *      "message": "Login Success",
 *      "token" : "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTU3OTUyOTA5MywiZXhwIjoxNTc5NTMyNjkzLCJuYmYiOjE1Nzk1MjkwOTMsImp0aSI6IjRjMGFidFZkU3dMbUtsVkwiLCJzdWIiOjEsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.RYqk1XdRG7DJczgciRYz-9IqO024fyk2DVnUdqsvLKo"
 * }
 * @apiSuccessExample {json} FAIL
 * HTTP/1.1 401 UNAUTHORIZED
 * {
 *      "success": false,
 *      "message": "Login Failed"
 * }
 * 
 */

 /**
 * @api {post} /login 02-register
 * @apiVersion 1.0.0
 * @apiName User Register
 * @apiGroup 01-Users
 *
 * 
 * 
 * @apiParamExample {json} PARAMETER
 * {
 *      "name": "Syahril Ardi",
 *      "email": "syahrilardi@aeeeeeng.com",
 *      "password": "ikipassword",
 *      "password_confirmation": "ikipassword"
 * }
 * 
 * @apiSuccessExample {json} REQUIRED FIELD INPUT
 * HTTP/1.1 400 BAD REQUEST
 * {
 *      "success": false,
 *      "message": {
 *           "name": [
 *               "The name field is required."
 *           ],
 *           "email": [
 *               "The email field is required."
 *           ],
 *         "password": [
 *              "The password field is required."
 *           ]
 *        }
 * }
 *  @apiSuccessExample {json} SUCCESS
 *  HTTP/1.1 200 OK
 * {
 *      "success": true,
 *      "message": 
 *      {
 *          "user": {
 *                       "name": "emboh wes",
 *                       "email": "syahrilardi@yahoo.coma",
 *                       "updated_at": "2020-01-20 16:05:25",
 *                       "created_at": "2020-01-20 16:05:25",
 *                       "id": 3
 *                   },
 *           "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9yZWdpc3RlciIsImlhdCI6MTU3OTUzNjMyNiwiZXhwIjoxNTc5NTM5OTI2LCJuYmYiOjE1Nzk1MzYzMjYsImp0aSI6Im9tRVdoWUFyNEhwVXhQTHEiLCJzdWIiOjMsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.j-FoJUXD_7j0AQL8AbqPhK-N5-CUtJSMKxukpDjNfMA"
 *       }
 * }
 * @apiSuccessExample {json} FAIL
 * HTTP/1.1 500 INTERNAL SERVER ERROR
 * {
 *      "success": false,
 *      "message": "string value"
 * }
 * 
 */