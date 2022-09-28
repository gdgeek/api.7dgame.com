<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Quality Metrics</title>


  <!-- Add Bootstrap and Bootstrap-Vue CSS to the <head> section -->
  <link type="text/css" rel="stylesheet" href="https://unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
  <link type="text/css" rel="stylesheet" href="https://unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

  <!-- Add Vue and BootstrapVue scripts just before the closing </body> tag -->
  <script src="https://unpkg.com/vue/dist/vue.min.js"></script>
  <script src="https://unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.js"></script>ß

  <link rel="icon" type="image/x-icon" href="../imgs/favicon.ico" />
</head>

<body>
  <div id="app">
    

  <div>
  <b-card title="Card Title" no-body>
    <b-card-header header-tag="nav">
      <b-nav card-header tabs>
        <b-nav-item active>Active</b-nav-item>
        <b-nav-item>Inactive</b-nav-item>
        <b-nav-item disabled>Disabled</b-nav-item>
      </b-nav>
    </b-card-header>

    <b-card-body class="text-center">
      <b-card-text>
        With supporting text below as a natural lead-in to additional content.
      </b-card-text>

      <b-button variant="primary">Go somewhere</b-button>
    </b-card-body>
  </b-card>
</div>


  <b-container class="bv-example-row">
        <b-row>
            <b-col>col</b-col>
            <b-col>col</b-col>
            <b-col>col</b-col>
            <b-col>col</b-col>
        </b-row>

        <b-row>
            <b-col cols="8">col-8</b-col>
            <b-col cols="4">col-4</b-col>
        </b-row>
    </b-container>
    <div>
      <div>
        <b-card>
          <b-media>
            <template #aside>
              <b-img blank blank-color="#ccc" width="64" alt="placeholder"></b-img>
            </template>

            <h5 class="mt-0">Media Title</h5>
            <p>
              Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin.
              Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. Fusce condimentum nunc
              ac nisi vulputate fringilla. Donec lacinia congue felis in faucibus.
            </p>
            <p>
              Donec sed odio dui. Nullam quis risus eget urna mollis ornare vel eu leo. Cum sociis natoque
              penatibus et magnis dis parturient montes, nascetur ridiculus mus.
            </p>

            <b-media>
              <template #aside>
                <b-img blank blank-color="#ccc" width="64" alt="placeholder"></b-img>
              </template>

              <h5 class="mt-0">Nested Media</h5>
              <p class="mb-0">
                Fusce condimentum nunc ac nisi vulputate fringilla. Donec lacinia congue felis in
                faucibus.
              </p>
            </b-media>
          </b-media>
        </b-card>
      </div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col>1 of 3</b-col>
          <b-col>2 of 3</b-col>
          <b-col>3 of 3</b-col>
          <b-col>3 of 3</b-col>
          <b-col>3 of 3</b-col>
        </b-row>
      </b-container>

    
    </div>
    

  </div>
  <script>
    new Vue({
      el: '#app',
      data() {
        return {
          projects: null
        }
      },
      mounted() {
        axios.get('http://localhost:8080/projects')
          .then(response => {
            if (response.data.code == 0) {
              this.projects = response.data.data
            }
          })
          .catch(error => {
            console.log(error)
            return []
          })
      }
    })
  </script>
</body>

</html>