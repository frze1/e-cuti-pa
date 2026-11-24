<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-dinas-green hover:bg-dinas-greenhover text-white border border-transparent rounded-none font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-dinas-green transition-colors']) }}>
    {{ $slot }}
</button>
