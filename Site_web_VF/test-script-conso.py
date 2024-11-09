import os
import time

def get_cpu_usage():
    with open('/proc/stat', 'r') as f:
        lines = f.readlines()
    for line in lines:
        if 'cpu ' in line:
            cpu_data = line.split()
            total_time = sum(int(i) for i in cpu_data[1:])
            idle_time = int(cpu_data[4])
            return total_time, idle_time
    return 0, 0

def get_temp():
    temp = os.popen("vcgencmd measure_temp").readline()
    return float(temp.replace("temp=","").replace("'C\n",""))

def get_volts():
    volts = os.popen("vcgencmd measure_volts").readline()
    return float(volts.replace("volt=","").replace("V\n",""))

def estimate_power_usage(cpu_usage):
    # Consommation de base (estimée) en watts
    base_power = 1.7  # W, consommation minimale au repos
    # Consommation additionnelle par % d'utilisation du CPU
    max_power = 3.6  # W, consommation maximale à pleine charge
    additional_power_perc = (max_power - base_power) / 100  # W par % d'utilisation du CPU

    power_usage = base_power + (cpu_usage * additional_power_perc)
    return power_usage

prev_total_time, prev_idle_time = get_cpu_usage()

while True:
    time.sleep(1)
    total_time, idle_time = get_cpu_usage()
    delta_total_time = total_time - prev_total_time
    delta_idle_time = idle_time - prev_idle_time

    cpu_usage = (1 - (delta_idle_time / delta_total_time)) * 100
    temp = get_temp()
    volts = get_volts()
    power_usage = estimate_power_usage(cpu_usage)

    print(f"CPU Usage: {cpu_usage:.2f}%, Temperature: {temp:.2f}C, Voltage: {volts:.4f}V, Estimated Power Usage: {power_usage:.2f}W")

    prev_total_time = total_time
    prev_idle_time = idle_time
