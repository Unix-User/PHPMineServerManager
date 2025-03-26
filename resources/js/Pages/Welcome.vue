<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, reactive, onUnmounted } from 'vue';
import axios from 'axios';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    shopItems: Array
});

const heroItems = [
    {
        title: "Imersão Medieval Inigualável",
        subtitle: "Aventure-se no maior servidor medieval de Uberlândia-MG. Construa, batalhe e desvende segredos em UdiaNIX.",
        image: "storage/landing-page/hero1.png",
        cta: {
            text: "Junte-se Agora",
            link: "/register",
            variant: "primary"
        }
    },
    {
        title: "Domine Facções e Crie Seu Legado",
        subtitle: "Lidere sua facção à glória, conquiste territórios e marque seu nome na história medieval de UdiaNIX.",
        image: "storage/landing-page/hero2.png",
        cta: {
            text: "Explore Facções",
            link: "/factionCommands",
            variant: "secondary"
        }
    },
    {
        title: "Mapa Interativo em Tempo Real",
        subtitle: "Visualize o mundo de UdiaNIX, planeje estratégias e encontre recursos valiosos com nosso mapa dinâmico.",
        image: "storage/landing-page/hero3.png",
        cta: {
            text: "Ver Mapa Online",
            link: "https://dynmap.udianix.com.br/?worldname=udianix.com.br&mapname=surface&zoom=5",
            variant: "secondary"
        }
    }
];

const features = [
    {
        title: "Comunidade Amigável e Ativa",
        description: "Faça parte de uma comunidade acolhedora e vibrante, pronta para novas aventuras e amizades.",
        icon: "users",
    },
    {
        title: "Servidor Estável e Sempre Online",
        description: "Desfrute de uma experiência de jogo contínua e 99.9% de uptime, garantindo que sua aventura nunca pare.",
        icon: "server",
    },
    {
        title: "Sistema de Facções Estratégico",
        description: "Domine a art da guerra e diplomacia com um sistema de facções profundo e envolvente.",
        icon: "flag",
    },
    {
        title: "Regras Claras para Jogo Justo",
        description: "Uma experiência equilibrada e respeitosa para todos, com regras transparentes e fáceis de seguir.",
        icon: "book",
    },
    {
        title: "Comandos Exclusivos e Poderosos",
        description: "Ferramentas de comando personalizadas para você construir, gerenciar e expandir seu reino medieval.",
        icon: "terminal",
    },
    {
        title: "Mapa Dinâmico e Detalhado",
        description: "Explore cada recanto de UdiaNIX com um mapa interativo e em tempo real, sempre atualizado.",
        icon: "map-location-dot",
    },
];

const aboutUs = {
    title: "Sobre Udianix Minecraft",
    description: "Uidanix Minecraft é a sua porta de entrada para o mundo medieval do Minecraft em Uberlândia-MG. Uma comunidade vibrante, um servidor dedicado e inúmeras aventuras esperam por você. Junte-se a nós e construa sua história!",
};

const serverStats = ref({
    uptime: "Calculando...",
    factionsCount: 0,
    playersOnline: 0,
    maxPlayers: 20,
    serverVersion: "Desconhecida",
    javaVersion: "Desconhecida",
    serviceStatus: "Calculando..."
});

const serverStatus = reactive({
    serverVersion: "",
    isServerOnline: false,
    jogadores: "",
    maxJogadores: 20,
    online: [],
    isLoading: true,
    getJavaMemoryUsage: "",
});

const fetchServerStatus = async () => {
    try {
        const [checkConnectionResponse, playerCountResponse, serverVersionResponse, getJavaMemoryUsageResponse] = await Promise.all([
            axios.get("/api/check-connection"),
            axios.get("/api/player-count"),
            axios.get("/api/server-version"),
            axios.get("/api/get-java-memory-usage")
        ]);

        serverStatus.getJavaMemoryUsage = Math.round(getJavaMemoryUsageResponse.data.success[0].success) + " MB";
        serverStatus.serverVersion = serverVersionResponse.data[0].success;
        serverStatus.isServerOnline = checkConnectionResponse.data.is_connected;
        serverStatus.jogadores = playerCountResponse.data[0].success;
        serverStatus.isLoading = false;
    } catch (error) {
        console.error(error);
        serverStatus.isLoading = false;
    }
};

onMounted(() => {
    fetchServerStatus();
});

const gameplayInfo = {
    title: "Como Começar a Jogar",
    steps: [
        "Abra o Minecraft Java Edition (1.18.2+)",
        "Vá em 'Multiplayer' e 'Adicionar Servidor'",
        "Insira: `mc.udianix.com.br`",
        "Selecione 'UdiaNIX Minecraft' e 'Entrar'",
        "Registre-se no jogo e inicie sua aventura!",
    ],
    extraInfo: "Visite nosso Discord para notícias, eventos e suporte da comunidade.",
    discordLink: "https://discord.gg/F9wYVYmqW3",
};

const testimonials = [
    {
        text: "UdiaNIX superou todas as minhas expectativas! Um servidor medieval incrível com uma comunidade acolhedora.",
        author: "LordBardock",
    },
    {
        text: "O sistema de facções é o coração do servidor. Estratégia e batalhas épicas definem UdiaNIX.",
        author: "LadySnow",
    },
    {
        text: "O mapa interativo me ajuda a planejar cada passo. Essencial para quem gosta de explorar e construir.",
        author: "KingArthas",
    },
];

const currentHeroIndex = ref(0);
const nextHero = () => currentHeroIndex.value = (currentHeroIndex.value + 1) % heroItems.length;
const prevHero = () => currentHeroIndex.value = (currentHeroIndex.value - 1 + heroItems.length) % heroItems.length;

const scrollToContent = () => {
    window.scrollTo({
        top: window.innerHeight,
        behavior: 'smooth'
    });
};

const countdown = ref({
    hours: 0,
    minutes: 0,
    seconds: 0
});

const updateCountdown = () => {
    let now = new Date();
    let targetDate = new Date();
    targetDate.setDate(now.getDate() + 1);
    targetDate.setHours(0, 0, 0, 0);

    let diff = targetDate.getTime() - now.getTime();

    let hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    let seconds = Math.floor((diff % (1000 * 60)) / 1000);

    countdown.value.hours = hours;
    countdown.value.minutes = minutes;
    countdown.value.seconds = seconds;
};

onMounted(() => {
    updateCountdown();
    const intervalId = setInterval(updateCountdown, 1000);

    onUnmounted(() => {
        clearInterval(intervalId);
    });
});

const scrollToSection = (sectionId, offset = 100) => {
    const section = document.getElementById(sectionId);
    if (section) {
        const y = section.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({
            top: y,
            behavior: 'smooth'
        });
    }
};
</script>

<template>
    <div class="landing-page-background">

        <Head title=" UdiaNIX Minecraft - Servidor Medieval #1 de Uberlândia-MG" />

        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-cover bg-center bg-no-repeat bg-fixed overflow-hidden">
            <!-- Video Background -->
            <div class="absolute inset-0 z-0">
                <iframe class="w-full h-full object-cover"
                    src="https://www.youtube.com/embed/xcjT271y0oE?autoplay=1&mute=1&loop=1&controls=0&playlist=xcjT271y0oE&modestbranding=1&disablekb=1&fs=0&cc_load_policy=0&cc_lang_pref=pt&rel=0"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
                <div class="absolute inset-0 bg-black/50"></div>
            </div>

            <!-- Hero Content -->
            <div class="relative z-10 text-center px-4">
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 animate-fade-in-down">
                        {{ heroItems[currentHeroIndex].title }}
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-300 mb-8 animate-fade-in-down delay-100">
                        {{ heroItems[currentHeroIndex].subtitle }}
                    </p>
                    <Link :href="heroItems[currentHeroIndex].cta.link"
                        :class="['btn', heroItems[currentHeroIndex].cta.variant === 'primary' ? 'btn-primary' : 'btn-secondary', 'btn-lg', 'animate-fade-in-down', 'delay-200']">
                        {{ heroItems[currentHeroIndex].cta.text }}
                    </Link>
                </div>
            </div>

            <!-- Bottom Controls Container -->
            <div class="absolute bottom-0 left-0 right-0 z-20 flex flex-col items-center space-y-4 pb-8">
                <!-- Hero Pagination Dots -->
                <div class="flex space-x-2">
                    <button v-for="(item, index) in heroItems" :key="index" 
                        @click="currentHeroIndex = index"
                        class="w-3 h-3 rounded-full transition-colors"
                        :class="{
                            'bg-white': currentHeroIndex === index,
                            'bg-white/30 hover:bg-white/50': currentHeroIndex !== index
                        }">
                    </button>
                </div>

                <!-- Scroll Down Icon -->
                <div class="animate-bounce">
                    <i class="fa-solid fa-chevron-down text-4xl text-white cursor-pointer" @click="scrollToContent"></i>
                </div>
            </div>
        </div>

        <!-- Server Highlights Section - Solid -->
        <section class="highlights-section py-20 bg-gray-900">
            <div class="container">
                <h2 class="section-title">Destaques do Servidor UdiaNIX</h2>
                <p class="section-subtitle">Descubra o que torna UdiaNIX a melhor escolha para sua aventura medieval no
                    Minecraft.</p>
                <div class="highlights-grid">
                    <div v-for="(stat, index) in [
                        { title: `${serverStatus.jogadores || '...'}/${serverStatus.maxJogadores} Jogadores Online`, description: 'Junte-se a uma comunidade medieval ativa e vibrante!', icon: 'users' },
                        { title: `Servidor ${serverStatus.isServerOnline ? 'Online' : 'Offline'}`, description: 'Disponibilidade garantida para sua aventura.', icon: 'server' },
                        { title: `Versão do Servidor: ${serverStatus.serverVersion || '...'}`, description: 'Sempre atualizado com as últimas novidades.', icon: 'code' }
                    ]" :key="index" class="highlight-card bg-gray-800/70 backdrop-blur-sm">
                        <i class="highlight-icon"
                            :class="{ 'fa-solid fa-users': stat.icon === 'users', 'fa-solid fa-server': stat.icon === 'server', 'fa-solid fa-code': stat.icon === 'code' }"></i>
                        <h3 class="highlight-title">{{ stat.title }}</h3>
                        <p class="highlight-description">{{ stat.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hero Carousel Section - Transparent -->
        <section class="carousel-section py-20">
            <div class="container">
                <h2 class="section-title">Explore o Mundo de UdiaNIX</h2>
                <p class="section-subtitle">Uma prévia das aventuras e paisagens que esperam por você em nosso servidor.
                </p>
            </div>
            <div class="carousel-wrapper">
                <div class="carousel-container">
                    <div v-for="(item, index) in heroItems" :key="index" class="carousel-item"
                        :class="{ 'active': currentHeroIndex === index }">
                        <img :src="item.image" :alt="item.title" class="carousel-image" />
                        <div class="carousel-content bg-black/30 backdrop-blur-sm">
                            <h3 class="carousel-title">{{ item.title }}</h3>
                            <p class="carousel-subtitle">{{ item.subtitle }}</p>
                            <Link :href="item.cta.link"
                                :class="['btn', item.cta.variant === 'primary' ? 'btn-primary' : 'btn-secondary', 'btn-carousel', 'rounded-md']">
                            {{ item.cta.text }}
                            </Link>
                        </div>
                    </div>
                </div>
                <div class="carousel-controls">
                    <button @click="prevHero" class="carousel-control-button bg-white/60 backdrop-blur-sm rounded-full">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button @click="nextHero" class="carousel-control-button bg-white/60 backdrop-blur-sm rounded-full">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Features Section - Solid -->
        <section class="detailed-features-section py-20 bg-gray-900">
            <div class="container">
                <h2 class="section-title text-center">Recursos Exclusivos que Você Vai Adorar</h2>
                <p class="section-subtitle text-center">Descubra as funcionalidades que tornam UdiaNIX único e
                    envolvente.
                </p>
                <div class="detailed-features-grid">
                    <div v-for="(feature, index) in features" :key="index"
                        class="detailed-feature-card bg-gray-800/70 backdrop-blur-sm">
                        <i class="detailed-feature-icon" :class="`fa-solid fa-${feature.icon.toLowerCase()}`"></i>
                        <h3 class="detailed-feature-title">{{ feature.title }}</h3>
                        <p class="detailed-feature-description">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gameplay Info Section - Transparent -->
        <section class="gameplay-section py-20">
            <div class="container">
                <div class="gameplay-grid">
                    <div>
                        <h2 class="section-title">Pronto para Começar sua Aventura?</h2>
                        <p class="section-subtitle">Siga estes passos simples e junte-se à comunidade UdiaNIX hoje
                            mesmo.
                        </p>
                        <ul class="gameplay-steps">
                            <li v-for="(step, index) in gameplayInfo.steps" :key="index" class="animate-fade-in-up"
                                :style="`animation-delay: ${index * 100}ms`">
                                <span class="step-number">{{ index + 1 }}.</span> {{ step }}
                            </li>
                        </ul>
                        <p class="gameplay-extra-info">{{ gameplayInfo.extraInfo }}</p>
                        <div class="gameplay-discord-button">
                            <Link :href="gameplayInfo.discordLink" target="_blank"
                                class="btn-discord btn-lg rounded-md">
                            <i class="fa-brands fa-discord mr-2"></i> Junte-se ao Discord
                            </Link>
                        </div>
                    </div>
                    <div class="gameplay-video-container aspect-video w-full max-w-[800px] mx-auto">
                        <iframe class="gameplay-video w-full h-full rounded-lg shadow-lg"
                            src="https://www.youtube.com/embed/xcjT271y0oE?si=Qd_Z9n8WmYwtbwNp"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </section>

        

        <!-- Shop Section - Transparent -->
        <section class="shop-section py-20 bg-transparent dark:bg-transparent">
            <div class="container">
                <!-- Cabeçalho com CTA forte -->
                <div class="text-center mb-16">
                    <h2 class="section-title-inverted mb-4 text-4xl font-bold">Potencialize sua Jornada no UdiaNIX!</h2>
                    <p class="section-subtitle-inverted max-w-2xl mx-auto mb-6 text-lg">
                        Desbloqueie vantagens exclusivas e apoie o servidor com nossos pacotes premium. Cada compra nos ajuda a manter o UdiaNIX online e em constante evolução!
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <Link href="#shop-grid" class="btn-primary btn-lg rounded-md">
                            <i class="fa-solid fa-arrow-down mr-2"></i>
                            Ver Pacotes
                        </Link>
                        <Link href="https://discord.gg/F9wYVYmqW3" target="_blank" class="btn-secondary btn-lg rounded-md">
                            <i class="fa-brands fa-discord mr-2"></i>
                            Tire suas Dúvidas
                        </Link>
                    </div>
                </div>

                <!-- Grid de Produtos com Destaques Visuais -->
                <div id="shop-grid" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="(item, index) in shopItems" :key="item.id"
                        class="shop-card group bg-white/60 dark:bg-gray-700/60 backdrop-blur-sm rounded-xl p-8 hover:shadow-2xl transition-all duration-300 ease-in-out h-full flex flex-col"
                        :style="`animation-delay: ${index * 100}ms`">

                        <!-- Badge de Destaque -->
                        <div v-if="index === 1" class="absolute top-0 right-0 bg-yellow-400 text-gray-900 px-4 py-2 rounded-bl-lg rounded-tr-xl text-sm font-bold animate-pulse">
                            Mais Popular!
                        </div>

                        <div class="flex flex-col items-center text-center flex-grow">
                            <!-- Imagem do Produto com Efeito Hover -->
                            <div class="relative mb-6">
                                <img :src="item.image" :alt="item.name"
                                    class="w-32 h-32 object-cover rounded-lg border-2 border-white/20 group-hover:scale-110 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>

                            <!-- Nome e Descrição -->
                            <h3 class="text-2xl font-bold mb-3 text-gray-900 dark:text-white">{{ item.name }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6 leading-relaxed flex-grow">{{ item.description }}</p>

                            <!-- Botão de Compra com Efeito Hover -->
                            <Link :href="route('shop')"
                                class="btn-primary btn-lg w-full flex items-center justify-center hover:bg-opacity-90 transition-colors duration-200 text-lg font-semibold rounded-md">
                                <i class="fa-solid fa-shopping-cart mr-2"></i>
                                <span>Comprar por R$ {{ (item.price / 100).toFixed(2) }}</span>
                            </Link>

                            <!-- Selo de Garantia e Benefícios -->
                            <div class="mt-6 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-shield-check mr-2"></i>
                                    Compra 100% Segura
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-solid fa-clock mr-2"></i>
                                    Entrega Imediata
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-solid fa-headset mr-2"></i>
                                    Suporte 24/7
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chamada de Ação Final com Contagem Regressiva -->
                <div class="text-center mt-16 bg-white/60 dark:bg-gray-700/60 p-8 rounded-xl backdrop-blur-sm">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Oferta Especial por Tempo Limitado!</h3>
                    <div class="flex justify-center gap-4 mb-6">
                        <div class="bg-white/60 dark:bg-gray-700/60 p-4 rounded-lg">
                            <span class="text-3xl font-bold text-yellow-400" id="countdown-hours">{{ String(countdown.hours).padStart(2, '0') }}</span>
                            <span class="block text-sm text-gray-500 dark:text-gray-400">Horas</span>
                        </div>
                        <div class="bg-white/60 dark:bg-gray-700/60 p-4 rounded-lg">
                            <span class="text-3xl font-bold text-yellow-400" id="countdown-minutes">{{ String(countdown.minutes).padStart(2, '0') }}</span>
                            <span class="block text-sm text-gray-500 dark:text-gray-400">Minutos</span>
                        </div>
                        <div class="bg-white/60 dark:bg-gray-700/60 p-4 rounded-lg">
                            <span class="text-3xl font-bold text-yellow-400" id="countdown-seconds">{{ String(countdown.seconds).padStart(2, '0') }}</span>
                            <span class="block text-sm text-gray-500 dark:text-gray-400">Segundos</span>
                        </div>
                    </div>
                    <Link href="#shop-grid" class="btn-primary btn-lg rounded-md">
                        <i class="fa-solid fa-bolt mr-2"></i>
                        Aproveite a Oferta!
                    </Link>
                </div>
            </div>
        </section>

        <!-- About Us Section - Transparent -->
        <section class="about-us-section py-20">
            <div class="container flex flex-col items-center text-center">
                <h2 class="section-title mb-6">{{ aboutUs.title }}</h2>
                <p class="section-subtitle max-w-2xl mb-8">{{ aboutUs.description }}</p>
                <div class="about-us-cta">
                    <Link :href="route('login')"
                        class="btn-primary btn-lg rounded-md hover:scale-105 transition-transform"
                        aria-label="Junte-se ao UdiaNIX" title="Crie sua conta e comece a jogar">
                    <i class="fa-solid fa-gamepad mr-2"></i>
                    Jogar Agora
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer Section - Solid -->
        <footer class="footer-section py-12 bg-gray-900">
            <div class="container">
                <div class="footer-grid">
                    <div>
                        <h3 class="footer-title">Links Úteis</h3>
                        <ul class="footer-links">
                            <li><a href="http://minecraft.udianix.com.br/rules"><i class="fa-solid fa-book mr-2"></i>
                                    Regras
                                    do Servidor</a></li>
                            <li><a href="http://minecraft.udianix.com.br/factionCommands"><i
                                        class="fa-solid fa-shield-halved mr-2"></i> Comandos de Facções</a></li>
                            <li><a href="https://dynmap.udianix.com.br" target="_blank"><i
                                        class="fa-solid fa-map-location-dot mr-2"></i> Mapa Interativo</a></li>
                            <li><a href="http://minecraft.udianix.com.br/donations"><i
                                        class="fa-solid fa-heart mr-2"></i>
                                    Apoie o Servidor</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="footer-title">{{ aboutUs.title }}</h3>
                        <p class="footer-description">{{ aboutUs.description }}</p>
                    </div>
                    <div>
                        <h3 class="footer-title">Contato</h3>
                        <ul class="footer-links">
                            <li><a href="mailto:sac@udianix.com.br"><i class="fa-solid fa-envelope mr-2"></i>
                                    sac@udianix.com.br</a></li>
                            <li><a href="https://discord.gg/F9wYVYmqW3" target="_blank"><i
                                        class="fa-brands fa-discord mr-2"></i> discord.gg/F9wYVYmqW3</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="footer-title">Redes Sociais</h3>
                        <div class="footer-social-icons">
                            <a href="#" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                            <a href="https://discord.gg/F9wYVYmqW3" target="_blank" class="social-icon"><i
                                    class="fa-brands fa-discord"></i></a>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <div class="footer-copyright">
                        © 2024 UdiaNIX Minecraft. Todos os direitos reservados.
                    </div>
                    <div class="footer-credits">
                        Desenvolvido com <a href="https://laravel.com" target="_blank">Laravel</a> e <a
                            href="https://vuejs.org" target="_blank">Vue.js</a>.
                    </div>
                </div>
            </div>
        </footer>

        <!-- Global Login/Register Links -->
        <div v-if="canLogin" class="auth-links">
            <Link v-if="$page.props.auth.user" :href="route('home')">
            Painel
            </Link>
            <template v-else>
                <Link :href="route('login')">
                Entrar
                </Link>
                <Link v-if="canRegister" :href="route('register')">
                Registrar
                </Link>
            </template>
        </div>
    </div>
</template>
<style scoped>
/* General Styles */
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';

body {
    /* background-image: url('/storage/background.png'); - Removed from body */
    background-blend-mode: overlay;
}

.landing-page-background {
    /* Added class for background */
    background-image: url('/storage/background.png');
    @apply bg-no-repeat bg-cover bg-fixed;
    /* bg-fixed for fixed background */
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
        /* Reduced translateY for smoother animation */
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.5s ease-out forwards;
    /* Slightly faster animation */
}

.container {
    @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
}

.section-title {
    @apply text-3xl md:text-4xl font-bold mb-6 text-gray-900 dark:text-white text-center;
    /* Dark text for light, white for dark */
}

.section-subtitle {
    @apply text-lg text-gray-700 dark:text-gray-300 max-w-3xl mx-auto text-center;
    /* Adjusted gray shades for better contrast */
}

.section-title-inverted {
    @apply text-3xl md:text-4xl font-bold mb-6 text-white dark:text-white text-center;
    /* Inverted titles are always white */
}

.section-subtitle-inverted {
    @apply text-lg text-gray-200 dark:text-gray-200 max-w-3xl mx-auto text-center;
    /* Inverted subtitles are always light gray */
}

.btn {
    @apply px-6 py-3 rounded-md font-semibold text-center transition-all duration-300 hover:scale-105 inline-block border-2 border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-orange-500;
    /* Added focus styles and rounded-md, changed focus color */
}

.btn-primary {
    @apply bg-blue-500 hover:bg-blue-600 text-white dark:text-gray-900 dark:bg-orange-500 hover:dark:bg-orange-600 border-blue-500 hover:border-blue-600 dark:border-orange-500 hover:dark:border-orange-600;
    /* Light mode blue, Dark mode orange */
}

.btn-secondary {
    @apply bg-green-500 hover:bg-green-600 text-white dark:text-gray-900 dark:bg-teal-500 hover:dark:bg-teal-600 border-green-500 hover:border-green-600 dark:border-teal-500 hover:dark:border-teal-600;
    /* Light mode green, Dark mode teal */
}

.btn-lg {
    @apply text-lg px-8 py-4;
}

.btn-hero {
    @apply btn-lg text-xl;
}

.btn-discord {
    @apply bg-indigo-500 hover:bg-indigo-600 text-white dark:text-gray-900 dark:bg-purple-500 hover:dark:bg-purple-600 border-indigo-500 hover:border-indigo-600 dark:border-purple-500 hover:dark:border-purple-600;
    /* Light mode indigo, Dark mode purple */
}

/* Hero Section */
.hero-section {
    @apply relative overflow-hidden;
    /* Transparent background for both themes */
}

.hero-video {
    @apply absolute inset-0 w-full h-full object-cover z-0;
    /* Adjusted opacity for better balance in both themes */
    /* Even more transparent video */
}

.hero-overlay {
    @apply absolute inset-0 bg-black/30 dark:bg-black/70 z-10;
    /* Slightly adjusted overlay transparency */
}

.hero-content {
    @apply relative z-20 py-32 md:py-64 text-center text-gray-900 dark:text-white;
    /* Dark text for light, white for dark */
}

.hero-title {
    @apply text-4xl md:text-6xl font-bold mb-6;
}

.hero-subtitle {
    @apply text-lg md:text-2xl text-gray-500 dark:text-gray-300 mb-12 max-w-2xl mx-auto;
    /* Adjusted gray shades for subtitle */
}

.hero-buttons {
    @apply space-x-4;
}

/* Highlights Section */
.highlights-section {
    @apply py-16 bg-transparent dark:bg-transparent;
    /* Transparent background for highlights section */
    /* Solid background - changed to gray-800 for better contrast with transparent sections */
}

.highlights-grid {
    @apply grid grid-cols-1 md:grid-cols-3 gap-8 mt-12;
}

.highlight-card {
    @apply bg-white/60 dark:bg-gray-700/60 p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow text-center backdrop-blur-sm;
    /*  White/60 for light, gray-700/60 for dark, backdrop-blur */
    /* Even more transparent card background */
}

.highlight-icon {
    @apply text-green-500 dark:text-orange-400 text-4xl mb-4;
    /* Light mode green, Dark mode orange */
}

.highlight-title {
    @apply text-2xl font-bold mb-4 text-gray-900 dark:text-white;
    /* Dark text for light mode, white for dark mode */
}

.highlight-description {
    @apply text-gray-500 dark:text-gray-400;
    /* Adjusted gray shades */
}

/* Carousel Section */
.carousel-section {
    @apply py-20 bg-transparent dark:bg-transparent;
    /* Transparent background for carousel section */
    /* Transparent background - reduced transparency to /60 from /10 for better text readability */
}

.carousel-wrapper {
    @apply relative mt-12;
}

.carousel-container {
    @apply relative w-full overflow-hidden;
}

.carousel-item {
    @apply absolute transition-opacity duration-1000 ease-in-out inset-0 opacity-0;
}

.carousel-item.active {
    @apply opacity-100 relative;
}

.carousel-image {
    @apply block w-full h-[500px] object-cover rounded-xl shadow-lg;
}

.carousel-content {
    @apply absolute inset-0 bg-white/30 dark:bg-black/30 flex flex-col justify-center items-center text-center p-8 backdrop-blur-sm;
    /* White/30 for light, black/30 for dark, backdrop-blur */
    /* Even more transparent carousel content overlay */
}

.carousel-title {
    @apply text-3xl font-bold text-gray-900 dark:text-white mb-4 animate-fade-in-up;
    /* Dark text for light, white for dark */
}

.carousel-subtitle {
    @apply text-xl text-gray-500 dark:text-gray-300 mb-8 animate-fade-in-up delay-100 max-w-xl mx-auto;
    /* Adjusted gray shades for subtitle */
}

.btn-carousel {
    @apply animate-fade-in-up delay-200;
}

.carousel-controls {
    @apply absolute top-1/2 transform -translate-y-1/2 w-full flex justify-between px-4 md:px-8;
}

.carousel-control-button {
    @apply p-3 bg-white/40 dark:bg-gray-800/40 text-gray-900 dark:text-gray-100 rounded-full hover:bg-white/5 dark:hover:bg-gray-700 transition-colors hover:scale-110;
    /* White/40 for light, gray-800/40 for dark */
    /* Even more transparent control buttons */
}

.carousel-control-button i {
    @apply text-xl;
}

/* Detailed Features Section */
.detailed-features-section {
    @apply py-20 bg-transparent dark:bg-transparent;
    /* Transparent background for detailed features */
    /* Solid background - changed to gray-800 for better contrast with transparent sections */
}

.detailed-features-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-16;
}

.detailed-feature-card {
    @apply bg-white/60 dark:bg-gray-700/60 p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 backdrop-blur-sm;
    /* White/60 for light, gray-700/60 for dark, backdrop-blur */
    /* Even more transparent card background */
}

.detailed-feature-icon {
    @apply mb-4 text-green-500 dark:text-orange-400 text-3xl;
    /* Light mode green, Dark mode orange */
}

.detailed-feature-title {
    @apply text-xl font-semibold mb-2 text-gray-900 dark:text-white;
    /* Dark text for light mode, white for dark mode */
}

.detailed-feature-description {
    @apply text-gray-500 dark:text-gray-400;
    /* Adjusted gray shades */
}

.detailed-feature-link {
    @apply mt-3 inline-flex items-center text-green-500 dark:text-orange-400 hover:text-green-600 dark:hover:text-orange-500 transition-colors font-semibold;
    /* Light mode green, Dark mode orange, adjusted hover */
}

/* Gameplay Section */
.gameplay-section {
    @apply py-20 text-gray-900 dark:text-white bg-white/60 dark:bg-gray-800/60;
    /* Transparent background for gameplay section */
    /* Transparent background - reduced transparency to /60 from /10 for better text readability */
}

.gameplay-grid {
    @apply container grid grid-cols-1 lg:grid-cols-2 gap-16 items-center;
}

.gameplay-steps {
    @apply list-decimal pl-5 mt-8 space-y-4 text-lg text-gray-500 dark:text-gray-400;
    /* Adjusted gray shades */
}

.gameplay-steps li {
    @apply relative pl-6;
}

.step-number {
    @apply absolute left-0 top-0 font-semibold text-green-500 dark:text-orange-400;
    /* Light mode green, Dark mode orange */
}

.gameplay-extra-info {
    @apply mt-8 text-gray-500 dark:text-gray-400;
    /* Adjusted gray shades */
}

.gameplay-discord-button {
    @apply mt-6;
}

.gameplay-image-container {
    @apply flex justify-center;
}

.gameplay-image {
    @apply rounded-xl shadow-lg max-w-full h-auto opacity-50;
    /* Slightly more transparent image */
}

/* Testimonials Section */
.testimonials-section {
    @apply py-16 bg-transparent dark:bg-transparent;
    /* Transparent background for testimonials */
    /* Solid background - changed to gray-800 for better contrast with transparent sections */
}

.testimonials-grid {
    @apply mt-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8;
}

.testimonial-card {
    @apply p-6 bg-white/60 dark:bg-gray-700/60 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center relative backdrop-blur-sm;
    /* White/60 for light, gray-700/60 for dark, backdrop-blur */
    /* Even more transparent card background */
}

.testimonial-quote-icon {
    @apply absolute top-4 left-4 text-gray-300 dark:text-gray-600 text-2xl opacity-20;
    /* Adjusted icon opacity and color, lighter gray for light */
    /* Slightly more transparent icon */
}

.testimonial-text {
    @apply text-gray-500 dark:text-gray-400 italic mb-4;
    /* Adjusted gray shades */
}

.testimonial-author {
    @apply font-semibold text-green-500 dark:text-orange-400;
    /* Light mode green, Dark mode orange */
}

/* About Us Section */
.about-us-section {
    @apply py-20 text-gray-900 dark:text-white bg-white/60 dark:bg-gray-800/60;
    /* Transparent background for about us, dark text for light, white for dark */
    /* Transparent background - reduced transparency to /60 from /10 for better text readability */
}

.about-us-cta {
    @apply mt-12;
}

/* Footer Section */
.footer-section {
    @apply text-gray-500 dark:text-gray-400 py-12;
    /* Transparent background for footer, adjusted text color */
    /* Solid background - changed to gray-800 for better contrast with transparent sections */
}

.footer-grid {
    @apply grid grid-cols-1 md:grid-cols-4 gap-12 container;
}

.footer-title {
    @apply text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4;
    /* Darker gray for title in light mode */
}

.footer-description {
    @apply text-gray-500 dark:text-gray-400;
    /* Adjusted gray shades */
}

.footer-links {
    @apply space-y-2;
}

.footer-links li a {
    @apply text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-orange-400 transition-colors block inline-flex items-center;
    /* Adjusted gray shades, light mode green, dark mode orange hover */
}

.footer-social-icons {
    @apply flex space-x-4 justify-center md:justify-start;
}

.social-icon {
    @apply text-gray-500 dark:text-gray-400 hover:text-green-500 dark:hover:text-orange-400 transition-colors inline-flex;
    /* Adjusted gray shades, light mode green, dark mode orange hover */
}

.social-icon i {
    @apply text-lg;
}

.footer-bottom {
    @apply mt-12 pt-6 border-t border-gray-300/10 dark:border-gray-700/10 text-sm flex flex-col sm:flex-row justify-between items-center container;
    /* Adjusted border color, lighter gray for light */
    /* Even more transparent border */
}

.footer-copyright {
    @apply text-center text-gray-500 dark:text-gray-500 sm:text-left;
    /* Adjusted gray shades */
}

.footer-credits {
    @apply mt-4 text-center text-gray-500 dark:text-gray-500 sm:mt-0 sm:text-left;
    /* Adjusted gray shades */
}

.footer-credits a {
    @apply text-blue-500 hover:text-blue-600 dark:hover:text-teal-400 focus:outline focus:outline-2 focus:outline-blue-500 dark:focus:outline-teal-500;
    /* Light mode blue, Dark mode teal, adjusted focus */
}

/* Auth Links */
.auth-links {
    @apply sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10 hidden sm:block;
}

.auth-links>* {
    @apply font-semibold text-green-500 hover:text-green-600 dark:text-orange-400 dark:hover:text-orange-500 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 ml-4;
    /* Light mode green, Dark mode orange, adjusted hover */
}

.auth-links>*:first-child {
    @apply ml-0;
}

/* Utility function for feature icons - if you need more icons, expand here */
.detailed-feature-icon.icon-rules {}

.detailed-feature-icon.icon-commands {}

.detailed-feature-icon.icon-map {}

.detailed-feature-icon.icon-support {}
</style>
