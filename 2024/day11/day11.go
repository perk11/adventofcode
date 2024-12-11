package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		stonesByNumberCounts := map[int]int{}
		stonesByNumberCounts = make(map[int]int)
		for _, number := range strings.Fields(string(fileContents)) {
			numberInt, err := strconv.Atoi(number)
			if err != nil {
				panic(err)
			}
			stonesByNumberCounts[numberInt] = 1
		}
		for blinkNumber := 0; blinkNumber < 25; blinkNumber++ {
			stonesByNumberCounts = blink(stonesByNumberCounts)
		}
		total := 0
		for _, count := range stonesByNumberCounts {
			total += count
		}
		println(total)

		for blinkNumber := 0; blinkNumber < 75; blinkNumber++ {
			stonesByNumberCounts = blink(stonesByNumberCounts)
		}
		total = 0
		for _, count := range stonesByNumberCounts {
			total += count
		}
		println(total)
	}
}
func blink(stoneNumbersByCount map[int]int) map[int]int {
	result := make(map[int]int)
	for number, count := range stoneNumbersByCount {
		if number == 0 {
			incrementOrCreate(&result, 1, count)
		} else {
			numberString := strconv.Itoa(number)
			if len(numberString)%2 == 0 {
				leftNumber, err := strconv.Atoi(numberString[:len(numberString)/2])
				if err != nil {
					panic(err)
				}
				rightNumber, err := strconv.Atoi(numberString[len(numberString)/2:])
				if err != nil {

					panic(err)
				}

				incrementOrCreate(&result, leftNumber, count)
				incrementOrCreate(&result, rightNumber, count)
			} else {
				incrementOrCreate(&result, number*2024, count)
			}
		}
	}
	return result
}
func incrementOrCreate(counts *map[int]int, index int, amount int) {
	_, exists := (*counts)[index]
	if exists {
		(*counts)[index] += amount
	} else {
		(*counts)[index] = amount
	}
}
