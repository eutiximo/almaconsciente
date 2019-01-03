const gulp = require("gulp"),
      sass = require("gulp-sass"),
      clean = require("gulp-clean"),
      through = require("through2"),
      minify = require("gulp-minify"),
      include = require("gulp-include"),
      htmlReplace = require("gulp-html-replace"),
      autoprefixer = require("gulp-autoprefixer"),
      browserSync = require("browser-sync").create();

const devPath = "./project",
      distPath = "./wordpress/wp-content/themes/almaconsciente";

/*
 * Compilar SASS / SCSS a CSS
 */
gulp.task("compile:sass", () => {
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
    .pipe(gulp.dest(`${distPath}/styles/`))
    .pipe(browserSync.stream());
});

/*
 * Compilar includes en "main.js"
 */
gulp.task("compile:js", () => {
    var src = `${devPath}/_dev/scripts/*.js`,
        dest = `${devPath}/scripts/`;
    
    gulp.src(src)
    .pipe(include()).on("error", console.log)
    .pipe(gulp.dest(dest));
});

/*
 * Sincromizar navegadores y hacer livereload
 */
gulp.task("browserSync", () => {
    browserSync.init({
        injectChanges: true,
        notify: true,
        proxy: "almaconsciente.test"
    });
    
    gulp.watch([
        `${distPath}/**/*.php`,
        `${distPath}/**/*.js`,
        `${distPath}/**/*.twig`,
        `${distPath}/media/**/*.*`,
    ]).on("change", browserSync.reload);
});

/*
 * Tarea para observar cambios en los archivos y ejecutar tareas
 */
gulp.task("watchers", () => {
    gulp.watch(`${devPath}/_dev/**/*.js`, ["compile:js"]);
    gulp.watch(`${devPath}/_dev/**/*.scss`, ["compile:sass"]);
    
    //Watcher especial con reglas especificas.
    gulp.watch(`${devPath}/**/*.*`, (file) => {
        var pipePath, splitPath;
        
        pipePath = file.path.replace(/\\/g, "/");
        pipePath = /\/project\/?(\w*.*)/gm.exec(pipePath)[1];
        splitPath = pipePath.split("/");
        
        if (splitPath.includes("libs")) {
            gulp.src(`${distPath}/libs/*`).pipe(clean({forse: true}));
            console.log("Delete all files \"libs\" folder");
            
            setTimeout(function () {
                gulp.src(`${devPath}/libs/**/*.*`).pipe(gulp.dest(`${distPath}/libs/`));
                console.log("Migrate and update all \"libs\" success");
            }, 5000);
            
        } else if (!splitPath.includes("_dev")) {
            let completePath = /^(.*?)[^\/]+(?=\/$|$)/g.exec(pipePath)[1];
            
            gulp.src(file.path).pipe(gulp.dest(`${distPath}/${completePath}`));
            console.log(`Transfer file success: ${distPath}/${pipePath}`);
            
        } else {
            console.log(`A file from "_dev" is updated.`);
        }
    });
});

/*
 * Funcion para hacer un build optimizado del proyecto
 */
gulp.task("build:dist", () => {
    
    console.log("Build dist project");
    
});

/*
 * Tarea para ejecutar tareas por default
 */
gulp.task("default", ["watchers", "browserSync"]);