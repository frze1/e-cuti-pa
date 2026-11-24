@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-dinas-green dark:focus:border-dinas-green focus:ring-1 focus:ring-dinas-green dark:focus:ring-dinas-green rounded-none shadow-xs text-sm py-2 px-3 border']) }}>
