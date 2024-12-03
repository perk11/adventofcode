package main

import (
	"fmt"
	"math"
	"os"
	"slices"
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
		lines := strings.Split(string(fileContents), "\n")
		list1 := make([]int, len(lines))
		list2 := make([]int, len(lines))
		for index, line := range lines {
			split := strings.Fields(line)
			list1[index], _ = strconv.Atoi(strings.Trim(split[0], " "))
			list2[index], _ = strconv.Atoi(strings.Trim(split[1], " "))
		}
		slices.Sort(list1)
		slices.Sort(list2)
		var difference int = 0
		for index, _ := range list1 {
			difference += int(math.Abs(float64(list1[index] - list2[index])))
		}
		println(difference)
	}
}
