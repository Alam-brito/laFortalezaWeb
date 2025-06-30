<template>
    <section class="bg-gray-100 dark:bg-gray-900">
        <div class="lg:col-start-2">
            <img class="w-48 h-auto mx-auto lg:w-64" src="assets/images/logo.png" alt="La Fortaleza Logo" />
        </div>
        <div class="py-1 px-4 mx-auto max-w-screen-xl text-center lg:py-2 lg:px-2">
            <h1
                class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-800 dark:text-gray-100 md:text-5xl lg:text-6xl">
                AgroVeterinaria La Fortaleza
            </h1>
            <p class="animated-text">
                Al cuidado de tus animales
            </p>
        </div>
    </section>
</template>

<style scoped>
.animated-text {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
    text-transform: uppercase;
    background: linear-gradient(90deg,
            #7AC943,
            /* Verde del logo */
            #FFD700,
            /* Amarillo del sol */
            #FF8800,
            /* Naranja del trigo */
            #A3D900,
            /* Verde claro */
            #FFAA00
            /* Amarillo más intenso */
        );
    background-size: 300% 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: animateBackground 5s infinite linear;
}

@keyframes animateBackground {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}
</style>
