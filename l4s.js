/*
l4s.me link shortening worker
Cloudflare worker, JS
Use on route:
     *l4s.me*
© 2020 Jason Chua, Connor Coddington

Changelog:
07.2020 - Initial Creation
*/
async function handleRequest(request) {
     const status_code = 301; //Perma Redirect
     const hostname = "https://" + HOSTNAME;
     let destination = "";

     //The code
     path = new URL(request.url).pathname
     //"/Adsfsoioh"
     try {
          //First check for slug (Is the first character cap?)
          if (path[1] === path[1].toLowerCase()) {
               //lets verify its a slug
               if (path.substring(1, path.length).includes("/")) {
                    //Thats no slug!
                    //Thats a deathstar
                    throw exception
               }
               //Its a slug! Assemble the destination
               destination = hostname + path
          } else {
               //Its not a slug 
               //Determin Type
               type = "";
               id = 0;
               switch (path[1]) {
                    case "A":
                         //Its an Article / Post
                         type = "p"
                         break
                    case "P":
                         //Its a Page
                         type = "page_id"
                         break
                    case "M":
                         //Its Media / Attachment
                         type = "attachment_id"
                         break
                    default:
                         throw exception
               }
               //Find ID
               encoded_id = path.substring(2, path.length);

               id = baseN.decode(encoded_id)
               //Assemble dest
               //"https://layers.media/?p=2117", Page with ID 2117
               destination = hostname + "/?" + type + "=" + id;
          }

     }
     catch {
          //The above will fail if path is empty
          url = "https://l4s.me" + path
          const response = await fetch(url, {
               headers: {
                    'content-type': 'text/html;charset=UTF-8',
               }
          })
          return response
          return Response.redirect("https://github.com/rebel1804/WES-QOR-FOHX-PEZGF", 302)
     }

     return Response.redirect(destination, status_code)
}

addEventListener('fetch', async event => {
     event.respondWith(handleRequest(event.request))
})


const baseN = {
     charset: '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'
          .split(''),
     N: 56,
     //We dont use encode here, but its copy and pasted
     //https://lowrey.me/encoding-decoding-base-62-in-es6-javascript/
     encode: integer => {
          if (integer === 0) {
               return 0;
          }
          let s = [];
          while (integer > 0) {
               s = [baseN.charset[integer % baseN.N], ...s];
               integer = Math.floor(integer / baseN.N);
          }
          return s.join('');
     },
     decode: chars => chars.split('').reverse().reduce((prev, curr, i) =>
          prev + (baseN.charset.indexOf(curr) * (baseN.N ** i)), 0)
};
/*
Some IDs (Worked Example)
Post 2368 -> Li
     l4s.me/ALi
Attachment 2114 -> FL
     l4s.me/MFL
Page 77 ->3p
     l4s.me/P3p
*/