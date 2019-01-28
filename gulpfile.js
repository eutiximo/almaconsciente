const gulp = require("gulp"),
      sass = require("gulp-sass"),
      clean = require("gulp-clean"),
      minify = require("gulp-minify"),
      include = require("gulp-include"),
      htmlReplace = require("gulp-html-replace"),
      autoprefixer = require("gulp-autoprefixer"),
      browserSync = require("browser-sync").create(),
      devPath = "./wordpress/wp-content/themes/dev.almaconsciente",
      distPath = "./wordpress/wp-content/themes/almaconsciente";

/* Compilar SASS a CSS */
function compileSass() {
    var src = `${devPath}/_dev/scss/*.scss`,
        dest = `${devPath}/styles/`;
    
    gulp.src(src)
    .pipe(sass({
        outputStyle: "expanded"
    }))
    .pipe(autoprefixer({
        versions: ["last 2 browsers"]
    }))
    .pipe(gulp.dest(dest))
    .pipe(browserSync.stream());
}

/* Compilar javascript */
function compileJS() {
    var src = `${devPath}/_dev/scripts/*.js`,
        dest = `${devPath}/scripts/`;
    
    gulp.src(src)
    .pipe(include()).on("error", console.log)
    .pipe(gulp.dest(dest));
}

/* Mover librerias NPM a libs for dev */
function copyLibs() {
    gulp.src(`${devPath}/_dev/libs/*`).pipe(clean({forse: true}));
    
    setTimeout(function () {
        gulp.src(`./node_modules/bootstrap/dist/**/*.*`).pipe(gulp.dest(`${devPath}/_dev/libs/bootstrap/`));
        gulp.src(`./node_modules/jquery/dist/**/*.*`).pipe(gulp.dest(`${devPath}/_dev/libs/jquery/`));
        gulp.src(`./node_modules/owl.carousel/dist/**/*.*`).pipe(gulp.dest(`${devPath}/_dev/libs/owl-carousel/`));
    }, 5000);
}

/* Vigilar cambios en archivos y ejecutar tareas */
function watchers() {
    gulp.watch(`${devPath}/_dev/**/*.js`, ["compile:js"]);
    gulp.watch(`${devPath}/_dev/**/*.scss`, ["compile:sass"]);
}

/* Correr un navegador con livereload e inyeccion de dependencias */
function fnBrowserSync() {
    browserSync.init({
        injectChanges: true,
        notify: true,
        proxy: "almaconsciente.test"
    });
    
    gulp.watch([
        `${devPath}/**/*.php`,
        `${devPath}/**/*.js`,
        `${devPath}/**/*.twig`,
        `${devPath}/media/**/*.*`,
    ]).on("change", browserSync.reload);
}

/* Compilar todo el sitio y optimizar y sofuscar sus elementos */
function buildDist() {
    var destProject = `./project/`;
    gulp.src(`./project/*`).pipe(clean({forse: true}))
    
    setTimeout(function () {
        gulp.src(`${devPath}/**/*.*`).pipe(gulp.dest(`./project/`));
    }, 3000);
}

/*
 * Lista de tareas Gulp
 */
gulp.task("compile:sass", compileSass);
gulp.task("compile:js", compileJS);
gulp.task("copy-libs", copyLibs);
gulp.task("browserSync", fnBrowserSync);
gulp.task("watchers", watchers);
gulp.task("build:dist", buildDist);

gulp.task("default", ["watchers", "browserSync"]);